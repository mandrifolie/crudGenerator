<?php
namespace CrudGenerator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DataObject
{
    public $module          = null;
    public $entity          = null;
    public $metadata        = null;
    public $writeAction     = null;
    public $namespace       = null;
    public $directory       = null;

    /*
    private $_actions         = null;

    private $_routesPrefixe   = null;
    private $_parts           = null;
    private $_className       = null;
    private $_entityNamespace = null;
    private $_viewPath        = null;
    private $_controllerPath  = null;
    private $_namespacePath   = null;*/

    public function setModule($value)
    {
        $this->module = $value;
        return $this;
    }
    public function setEntity($value)
    {
        $this->entity = $value;
        return $this;
    }
    public function setMetadata(ClassMetadataInfo $value)
    {
        $this->metadata = $value;
        return $this;
    }
    public function setWriteAction($value)
    {
        $this->writeAction = $value;
        return $this;
    }
    public function setNamespace($value)
    {
        $this->namespace = $value;
        return $this;
    }
    public function setDirectory($value)
    {
        $this->directory = $value;
        return $this;
    }
/*
    public function setActions($value)
    {
        $this->_actions = $value;
        return $this;
    }
    public function setRoutesPrefixe($value)
    {
        $this->_routesPrefixe = $value;
        return $this;
    }
    public function setParts($value)
    {
        $this->_parts = $value;
        return $this;
    }
    public function setClassName($value)
    {
        $this->_className = $value;
        return $this;
    }
    public function setEntityNamespace($value)
    {
        $this->_entityNamespace = $value;
        return $this;
    }
    public function setViewPath($value)
    {
        $this->_viewPath = $value;
        return $this;
    }
    public function setControllerPath($value)
    {
        $this->_controllerPath = $value;
        return $this;
    }
    public function setNamespacePath($value)
    {
        $this->_namespacePath = $value;
        return $this;
    }
*/

    public function getModule()
    {
        return $this->module;
    }
    public function getEntity()
    {
        return $this->entity;
    }
    public function getEntityName()
    {
        if(!strrchr($this->entity, '\\')) {
            return $this->entity;
        } else {
            return str_replace('\\', '', strrchr($this->entity, '\\'));
        }
    }
    public function getMetadata()
    {
        return $this->metadata;
    }
    public function getWriteAction()
    {
        return $this->writeAction;
    }
    public function getNamespace()
    {
        return $this->namespace;
    }
    public function getNamespacePath()
    {
        return str_replace('\\', '/', $this->namespace);
    }
    public function getDirectory()
    {
        return $this->directory;
    }
    /*
    public function getActions()
    {
        return $this->_actions;
    }
    public function getRoutesPrefixe()
    {
        return $this->_routesPrefixe;
    }
    public function getParts()
    {
        return $this->_parts;
    }
    public function getClassName()
    {
        return $this->_className;
    }
    public function getEntityNamespace()
    {
        return $this->_entityNamespace;
    }
    public function getViewPath()
    {
        return $this->_viewPath;
    }
    public function getControllerPath()
    {
        return $this->_controllerPath;
    }
    public function getNamespacePath()
    {
        return $this->_namespacePath;
    }*/
}