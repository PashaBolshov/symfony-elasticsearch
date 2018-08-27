<?php

namespace Pavlik\ElasticsearchBundle\Transformer;

use Pavlik\ElasticsearchBundle\Annotation\Metadata;
use Pavlik\ElasticsearchBundle\Annotation\EmbeddedMetadata;
use Elastica\Document;
use Pavlik\ElasticsearchBundle\Annotation\JoinName;
use Pavlik\ElasticsearchBundle\Annotation\JoinParent;

class Transformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transformToEntity(Document $documet, Metadata $metadata)
    {
        $data       = $documet->getData();
        $parameters = $documet->getParams();
        return $this->setEntityData($data, $parameters, $metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function transformToDoc($entity, Metadata $metadata)
    {
        list($data, $params) = $this->getEntityData($entity, $metadata);

        $doc = new Document();
        $doc->setData($data);

        $doc->setType($metadata->getType());
        $doc->setIndex($metadata->getIndexName());
        $params = array_merge($doc->getParams(), $params);
        $doc->setParams($params);

        return $doc;
    }

    /**
     * @param object $entity
     * @param Metadata $metadata
     * @return array
     */
    protected function getEntityData($entity, Metadata $metadata)
    {
        $entitityProperties = $metadata->getEntityProperties();
        $data   = [];
        $params = [];
        foreach($entitityProperties as $entitityProperty => $elasticProperty) {
            $value = $metadata->getEntityPropertyValue($entity, $entitityProperty);
            if( is_null($value) ) continue;
            
            if( $elasticProperty instanceof EmbeddedMetadata ) {
                list($values) = $this->getEntityData($value, $elasticProperty->getMetadata());
                $prefix = $elasticProperty->getPrefix();
                foreach($values as $key => $value) {
                    $data[$prefix . $key] = $value;
                }
            } elseif( $elasticProperty instanceof JoinName ) {
                $elasticProperty = $metadata->getElasticJoinPropertyName();
                $data[$elasticProperty] = $data[$elasticProperty] ?? [];
                $data[$elasticProperty]['name'] = $value;

            } elseif( $elasticProperty instanceof JoinParent && ! is_null($value) ) {
                $elasticProperty = $metadata->getElasticJoinPropertyName();
                $data[$elasticProperty] = $data[$elasticProperty] ?? [];
                $data[$elasticProperty]['parent'] = $value;
            } else {

                if( $metadata->isElasticParameter($elasticProperty) ) {
                    $params[$elasticProperty] = $value;
                } else {
                    $data[$elasticProperty] = $value;
                }
            }
        }

        return [$data, $params];
    }

    /**
     * @param array $data
     * @param array $params
     * @param Metadata $metadata
     * @return object
     */
    public function setEntityData($data, $params, Metadata $metadata)
    {
        $class = $metadata->getClassName();
        $entity = new $class;

        $entitityProperties = $metadata->getEntityProperties();
        foreach($entitityProperties as $entitityProperty => $elasticProperty) {
            $value = null;
            if( $metadata->isElasticParameter($elasticProperty) ) {
                $source = $params;
            } else {
                $source = $data;
            }
            
            if( $elasticProperty instanceof EmbeddedMetadata ) {
                $emembedData = $source;
                if($prefix = $elasticProperty->getPrefix() ){
                    $emembedData = [];
                    array_walk($source, function($value, $key)use(&$emembedData, $prefix) {
                        $position = strpos($key, $prefix);
                        if( $position === 0) {
                            $key = substr($key, strlen($prefix));
                            $emembedData[$key] = $value;
                        }

                    }, $prefix);
                }

                if( ! empty($emembedData) ) {
                    $value = $this->setEntityData($emembedData, [], $elasticProperty->getMetadata());
                }
                
            } elseif( $elasticProperty instanceof JoinName ) {
                $elasticProperty = $metadata->getElasticJoinPropertyName();
                $joinData = $source[$elasticProperty];
                if( is_array($joinData) ) {
                    $value = $joinData['name'];
                } else {
                    $value = $joinData;
                }

            } elseif( $elasticProperty instanceof JoinParent ) {
                $elasticProperty = $metadata->getElasticJoinPropertyName();
                $joinData = $source[$elasticProperty];
                $value = $joinData['parent'] ?? null;
            } else {
                $value = $source[$elasticProperty] ?? null;
            }

            $metadata->setEntityPropertyValue($entity, $entitityProperty, $value);
        }

        return $entity;
    }
}