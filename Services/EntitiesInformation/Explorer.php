<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\EntitiesInformation;

class Explorer extends \obo\Object {

    const ANNOTATION_PREFIX = "obo-";

    /**
     * @var string[]
     */
    private $annotationsDefinitions = array();

    /**
     * @param string $className
     * @return string
     */
    private function propertiesClassNameForEntityWithClassName($className) {
        if (!\class_exists($propertiesClassName = $className . "Properties")) {
            foreach ($className::ancestorsClass() as $ancestorClass) {
                if ($ancestorClass === "obo\\Object") {
                    $propertiesClassName = "\\obo\\EntityProperties";
                    break;
                } elseif (\class_exists($ancestorClass::entityInformation()->propertiesClassName)) {
                    $propertiesClassName = $ancestorClass::entityInformation()->propertiesClassName;
                    break;
                }
            }
        }

        return $propertiesClassName;
    }

    /**
     * @param string $className
     * @return string
     */
    private function defaultRepositoryNameForEntityWithClassName($className) {
        $repositoryName = "";
        foreach (\explode("\\", $className) as $namePart) $repositoryName .= \ucfirst($namePart);
        return $repositoryName;
    }

    /**
     * @param mixed $annotationValue
     * @return array
     */
    private function standardizeAnnotationValue($annotationValue) {
        return ($annotationValue[0] instanceof \ArrayObject || $annotationValue[0] instanceof \Nette\ArrayHash)
                    ? (array) $annotationValue[0] : array($annotationValue[0]);
    }

    /**
     * @param string $annotationName
     * @param string $scope
     * @return boolean
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function existAnnotationWithNameForScope($annotationName, $scope) {
        if (\strpos($annotationName, self::ANNOTATION_PREFIX) !== 0) return false;
        if (!isset($this->annotationsDefinitions["{$scope}-{$annotationName}"])) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$annotationName}' for {$scope} does not exist");
        return true;
    }

    /**
     * @param string $annotationName
     * @param string $scope
     * @return \obo\Annotation\Definition
     */
    public function annotationClassWithNameForScope($annotationName, $scope) {
        return $this->existAnnotationWithNameForScope($annotationName, $scope) ? $this->annotationsDefinitions["{$scope}-{$annotationName}"] : null;
    }

    /**
     * @param string $annotationClassName
     * @return void
     */
    public function registerAnnotation($annotationClassName) {
        $this->annotationsDefinitions[$annotationClassName::scope() . "-obo-" . $annotationClassName::name()] = $annotationClassName;
    }

    /**
     * @param string $annotationClassName
     * @return void
     */
    public function unregisterAnnotation($annotationClassName) {
        unset($this->annotationsDefinitions[$annotationClassName::scope() . "-obo-" . $annotationClassName::name()]);
    }

    /**
     * @param string $annotationName
     * @param array $annotationValue
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     * @return void
     */
    private function processAnnotationWithNameAndValueForEntity($annotationName, array $annotationValue, \obo\Carriers\EntityInformationCarrier $entityInformation) {
        if (\is_null($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::ENTITY_SCOPE))) return;
        $annotation = new $annotationClass($entityInformation);
        $annotation->process($annotationValue);
        $entityInformation->annotations[] = $annotation;
    }

    /**
     * @param string $annotationName
     * @param array $annotationValue
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     * @param string $methodName
     * @return void
     */
    private function processAnnotationWithNameAndValueForMethodWithName($annotationName, array $annotationValue, \obo\Carriers\EntityInformationCarrier $entityInformation, $methodName) {
        if (\is_null($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::METHOD_SCOPE))) return;
        $annotation = new $annotationClass($entityInformation, $methodName);
        $annotation->process($annotationValue);
        $entityInformation->annotations[] = $annotation;
    }

    /**
     * @param string $annotationName
     * @param array $annotationValue
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return void
     */
    private function processAnnotationWithNameAndValueForProperty($annotationName, array $annotationValue, \obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        if (\is_null($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::PROPERTY_SCOPE))) return;
        $annotation = new $annotationClass($propertyInformation);
        $annotation->process($annotationValue);
        $propertyInformation->annotations[] = $annotation;
    }

    /**
     * @param string $className
     * @return array
     */
    protected function ancestorsForClassWithName($className) {
        $classes = [$className];

        foreach (\class_parents($className) as $class) {
            if ($class === "obo\\Object") break;
            array_unshift($classes, $class);
        }

        return $classes;
    }

    /**
     * @param string $entityClassName
     * @return array
     */
    protected function loadEntityAnnotationForEntityWithClassName($entityClassName) {
        $annotations = array();

        foreach($this->ancestorsForClassWithName($entityClassName) as $class) {
            $classAnnotations = array();

            foreach($class::getReflection()->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !==0) continue;
                $classAnnotations[$annotationName] = $this->standardizeAnnotationValue($annotationValue);
            }

            $annotations = array_replace_recursive($annotations, $classAnnotations);
        }

        return $annotations;
    }

    /**
     * @param string $methodName
     * @param string $entityClassName
     * @return array
     */
    protected function loadMethodAnnotationForMethodWithNameAndEntityWithClassName($methodName, $entityClassName) {
        $annotations = array();

        foreach($this->ancestorsForClassWithName($entityClassName) as $class) {
            if (!$class::getReflection()->hasMethod($methodName)) continue;
            $classAnnotations = array();

            foreach($class::getReflection()->getMethod($methodName)->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !==0) continue;
                $classAnnotations[$annotationName] = $this->standardizeAnnotationValue($annotationValue);
            }

            $annotations = array_replace_recursive($annotations, $classAnnotations);
        }

        return $annotations;
    }

    /**
     * @param string $propertyName
     * @param string $entityPropertiesClassName
     * @return array
     */
    protected function loadPropertyAnnotationForPropertyWithNameAndEntityPropertiesWithClassName($propertyName, $entityPropertiesClassName) {
        $annotations = array();

        foreach($this->ancestorsForClassWithName($entityPropertiesClassName) as $class) {
            if (!$class::getReflection()->hasProperty($propertyName)) continue;
            $classAnnotations = array();

            foreach($class::getReflection()->getProperty($propertyName)->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !==0) continue;
                $classAnnotations[$annotationName] = $this->standardizeAnnotationValue($annotationValue);
            }

            $annotations = array_replace_recursive($annotations, $classAnnotations);
        }

        return $annotations;
    }

    /**
     * @param string $entityClassName
     * @return \obo\Carriers\EntityInformationCarrier
     * @throws \obo\Exceptions\DefinitionException
     */
    public function exploreEntityWithClassName($entityClassName) {
        $entityInformation = new \obo\Carriers\EntityInformationCarrier(array(
            "className" => $entityClassName,
            "propertiesClassName" => $this->propertiesClassNameForEntityWithClassName($entityClassName),
            "managerName" => $managerName = $entityClassName . "Manager",
            "repositoryName" => $managerName::dataStorage()->existsRepositoryWithName($this->defaultRepositoryNameForEntityWithClassName($entityClassName)) ? $this->defaultRepositoryNameForEntityWithClassName($entityClassName) : null,
        ));

        foreach ($this->loadEntityAnnotationForEntityWithClassName($entityClassName) as $annotationName => $annotationValue) {
            try {
                $this->processAnnotationWithNameAndValueForEntity($annotationName, $annotationValue, $entityInformation);
            } catch (\obo\Exceptions\BadAnnotationException $exc) {
                $exc->foreseenFileError = $entityClassName::getReflection()->getFileName();
                $exc->foreseenLineError = $entityClassName::getReflection()->getStartLine();
                throw $exc;
            }
        }

        foreach ($entityClassName::getReflection()->getMethods() as $method) {

            foreach ($this->loadMethodAnnotationForMethodWithNameAndEntityWithClassName($method->name, $entityClassName) as $annotationName => $annotationValue) {
                try {
                    $this->processAnnotationWithNameAndValueForMethodWithName($annotationName, $annotationValue, $entityInformation, $method->name);
                } catch (\obo\Exceptions\BadAnnotationException $exc) {
                    $exc->foreseenFileError = $entityClassName::getReflection()->getMethod($method->name)->getFileName();
                    $exc->foreseenLineError = $entityClassName::getReflection()->getMethod($method->name)->getStartLine();
                    throw $exc;
                }

            }
        }

        $propertiesObjectClassName = $entityInformation->propertiesClassName;

        $propertiesMethodAccess = array();

        foreach ($propertiesObjectClassName::getReflection()->getMethods() as $method) {
            $methodName = $method->name;

            if ($method->class === "obo\\EntityProperties" OR $method->class === "obo\\Object" OR $method->class === "Nette\\Object") continue;

            if (!\preg_match("#^((get)|(set))[A-Z].+#", $methodName)) continue;

            $propertyName = lcfirst(\preg_replace("#^((get)|(set))#", "", $methodName));

            $propertiesMethodAccess[$propertyName][\preg_match("#^get[A-Z].+#", $methodName) ? "getterName" : "setterName"] = $methodName;
        }

        if (!\class_exists($propertiesObjectClassName)) throw new \obo\Exceptions\DefinitionException("Properties class for entity with name '{$entityInformation->className}' does not exist");

        foreach ($propertiesObjectClassName::getReflection()->getProperties() as $property) {
            if ($property->class === "obo\\EntityProperties" OR $property->class === "obo\\Object" OR $property->class === "Nette\\Object") continue;

            $propertyInformation = $entityInformation->addPropertyInformation(array(
                    "name" => $property->name,
                ));

            foreach ($this->loadPropertyAnnotationForPropertyWithNameAndEntityPropertiesWithClassName($property->name, $propertiesObjectClassName) as $annotationName => $annotationValue) {
                try {
                    $this->processAnnotationWithNameAndValueForProperty($annotationName, $annotationValue, $propertyInformation);
                } catch (\obo\Exceptions\BadAnnotationException $exc) {
                    $exc->foreseenFileError = $propertiesObjectClassName::getReflection()->getProperty($property->name)->getDeclaringClass()->getFileName();
                    $exc->foreseenLineError = $propertiesObjectClassName::getReflection()->getProperty($property->name)->getDeclaringClass()->getStartLine();
                    throw $exc;
                }
            }

            if(isset($propertiesMethodAccess[$property->name])) {
                foreach ($propertiesMethodAccess[$property->name] as $methodType => $methodName) {
                    if (!\is_null($propertyInformation->relationship)) throw new \obo\Exceptions\DefinitionException("Property with name '{$property->name}' defined as the relationship can not have getter or setter", null, null, $propertiesObjectClassName::getReflection()->getMethod($methodName)->getFileName(), $propertiesObjectClassName::getReflection()->getMethod($methodName)->getStartLine());
                    $propertyInformation->$methodType = $methodName;
                    $propertyInformation->{$methodType === "getterName" ? "directAccessToRead" : "directAccessToWrite"} = false;
                }

                unset($propertiesMethodAccess[$property->name]);
            }

        }

        foreach($propertiesMethodAccess as $propertyName => $propertyAccess) {
            $propertyInformation = $entityInformation->addPropertyInformation(array("name" => $propertyName));
            foreach ($propertyAccess as $methodType => $methodName) {
                $propertyInformation->$methodType = $methodName;
                $propertyInformation->{$methodType === "getterName" ? "directAccessToRead" : "directAccessToWrite"} = false;
            }
        }

        $entityManagerName = $entityInformation->managerName;

        if (!\is_null($entityInformation->repositoryName)) {
            $entityInformation->repositoryColumns = $entityManagerName::dataStorage()->columnsInRepositoryWithName($entityInformation->repositoryName);
        }

        $entityInformation->processInformation();

        return $entityInformation;
    }

}
