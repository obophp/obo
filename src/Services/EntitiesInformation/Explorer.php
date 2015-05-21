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
     * @var array
     */
    protected $annotationsDefinitions = [];

    /**
     * @var \obo\Carriers\EntityInformationCarrier[]
     */
    protected $entitiesInformations = [];

    /**
     * @return \obo\Carriers\EntityInformationCarrier[]
     */
    public function entitiesInformations() {
        return $this->entitiesInformations;
    }

    /**
     * @param string $annotationClassName
     * @param boolen $forced
     * @throws \obo\Exceptions\Exception
     */
    public function registerAnnotation($annotationClassName, $forced = false) {
        if (!$forced AND $this->existAnnotationWithNameForScope($annotationClassName::name(), $annotationClassName::scope())) throw new \obo\Exceptions\Exception ("Can't register annotation with name " . $annotationClassName::name() . " for scope " . $annotationClassName::scope() . ", is already registered");
        $this->annotationsDefinitions[$annotationClassName::scope() . "-obo-" . $annotationClassName::name()] = $annotationClassName;
    }

    /**
     * @param type $annotationClassName
     * @throws \obo\Exceptions\Exception
     */
    public function unregisterAnnotation($annotationClassName) {
        if (!$this->existAnnotationWithNameForScope($annotationClassName::name(), $annotationClassName::scope())) throw new \obo\Exceptions\Exception ("Can't unregister annotation with name " . $annotationClassName::name() . " for scope " . $annotationClassName::scope() . ", is not registered");
        unset($this->annotationsDefinitions[$annotationClassName::scope() . "-obo-" . $annotationClassName::name()]);
    }

    /**
     * @param string $annotationName
     * @param string $scope
     * @return boolean
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function existAnnotationWithNameForScope($annotationName, $scope) {
        if (\strpos($annotationName, self::ANNOTATION_PREFIX) !== 0) return false;
        if (isset($this->annotationsDefinitions["{$scope}-{$annotationName}"])) return true;
        throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$annotationName}' for {$scope} does not exist");
    }

    /**
     *
     * @param string $annotationName
     * @param string $scope
     * @return string
     */
    public function annotationClassWithNameForScope($annotationName, $scope) {
        return $this->existAnnotationWithNameForScope($annotationName, $scope) ? $this->annotationsDefinitions["{$scope}-{$annotationName}"] : null;
    }

    /**
     * @param array $dirPaths
     * @return \obo\Carriers\EntityInformationCarrier[]
     */
    public function analyze(array $dirPaths) {
        $entitiesClasses = [];

        foreach($dirPaths as $dirPath) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath), \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);
            $files = new \RegexIterator($iterator, '#^.+\.php$#', \RegexIterator::MATCH, \RegexIterator::USE_KEY);

            foreach($files as $fileName) {
                foreach ($this->findClasses(file_get_contents($fileName)) as $className) {
                    $reflection = new \ReflectionClass($className);
                    if ($reflection->isSubclassOf("\\obo\\Entity")) $entitiesClasses[] = $className;
                }
            }
        }

        foreach ($entitiesClasses as $entityClassName) {
            $this->entitiesInformations[$entityClassName] = $this->analyzeEntityWithClassName($entityClassName);
        }


        foreach ($entitiesClasses as $entityClassName) {
            $this->validateEntityWithClassName($entityClassName);
        }

        return $this->entitiesInformations;
    }

    /**
     * @param string $entityClassName
     * @return \obo\Carriers\EntityInformationCarrier
     */
    protected function analyzeEntityWithClassName($entityClassName) {
        $entityClassReflection = $entityClassName::getReflection();
        $entityInformation = new \obo\Carriers\EntityInformationCarrier();
        $entityInformation->className = $entityClassName;
        $entityInformation->propertiesClassName = $propertiesClassName = $this->propertiesClassNameForEntityWithClassName($entityClassName);
        $entityInformation->managerName = $this->managerClassNameForEntityWithClassName($entityClassName);
        $entityInformation->repositoryName = $this->defaultRepositoryNameForEntityWithClassName($entityClassName);
        $entityInformation->primaryPropertyName = $this->defaultPrimaryPropertyNameForEntityWithClassName($entityClassName);
        $entityInformation->isAbstract = $entityClassReflection->isAbstract();
        $entityInformation->file = $entityClassReflection->fileName;

        $propertiesClassReflection = $propertiesClassName::getReflection();

        $entityInformation->isPropertiesAbstract = $propertiesClassReflection->isAbstract();
        $entityInformation->propertiesFile = $propertiesClassReflection->fileName;

        foreach ($this->loadEntityAnnotationForEntityWithClassName($entityClassName) as $annotationName => $annotationValue) {
            if (\is_null($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::ENTITY_SCOPE))) continue;
            $annotation = new $annotationClass($entityInformation);
            $annotation->process($annotationValue);
            $entityInformation->annotations[] = $annotation;
        }

        foreach ($entityClassReflection->getMethods() as $method) {
            foreach ($this->loadMethodAnnotationForMethodWithNameAndEntityWithClassName($method->name, $entityClassName) as $annotationName => $annotationValue) {
                if (\is_null($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::METHOD_SCOPE))) continue;
                $annotation = new $annotationClass($entityInformation, $method->name);
                $annotation->process($annotationValue);
                $entityInformation->annotations[] = $annotation;
            }
        }

        $propertiesInformations = [];
        $propertiesMethodAccess = [];
        $classVariables =  \get_class_vars($entityInformation->propertiesClassName);

        foreach ($propertiesClassReflection->getMethods() as $method) {
            $methodName = $method->name;

            if ($method->class === "obo\\EntityProperties" OR $method->class === "obo\\Object") continue;

            if (!\preg_match("#^((get)|(set))[A-Z].+#", $methodName)) continue;

            $propertyName = lcfirst(\preg_replace("#^((get)|(set))#", "", $methodName));

            $propertiesMethodAccess[$propertyName][\preg_match("#^get[A-Z].+#", $methodName) ? "getterName" : "setterName"] = $methodName;
        }

        foreach ($propertiesClassReflection->getProperties() as $property) {
            if ($property->class === "obo\\EntityProperties" OR $property->class === "obo\\Object") continue;

            $propertyInformation = new \obo\Carriers\PropertyInformationCarrier();
            $propertyInformation->name = $property->name;
            $propertyInformation->varName = $property->name;
            $propertyInformation->columnName = $this->defaultColumnNamePropertiesForPropertyWithName($property->name, $entityClassName);
            $propertyInformation->persistable = $this->defaultPersistableValuePropertiesForPropertyWithName($property->name, $entityClassName);
            $propertyInformation->autoIncrement = $this->defaultAutoIncrementValueForPropertyWithName($property->name, $entityClassName);
            $propertyInformation->nullable = $this->defaultNullableValueForPropertyWithName($property->name, $entityClassName);

            if(isset($classVariables[$property->name])) $propertyInformation->defaultValue = $classVariables[$property->name];

            if(isset($propertiesMethodAccess[$property->name])) {

                foreach ($propertiesMethodAccess[$property->name] as $methodType => $methodName) {
                    $propertyInformation->$methodType = $methodName;
                    $propertyInformation->{$methodType === "getterName" ? "directAccessToRead" : "directAccessToWrite"} = false;
                }

                unset($propertiesMethodAccess[$property->name]);
            }

            $propertiesInformations[$propertyInformation->name] = $propertyInformation;
        }

        foreach($propertiesMethodAccess as $propertyName => $propertyAccess) {
            $propertyInformation = new \obo\Carriers\PropertyInformationCarrier();
            $propertyInformation->name = $propertyName;
            foreach ($propertyAccess as $methodType => $methodName) $propertyInformation->$methodType = $methodName;
            $propertiesInformations[$propertyInformation->name] = $propertyInformation;
        }

        foreach ($propertiesInformations as $propertyInformation) $propertyInformation->dataType = $this->defaultDataTypeForProperty($propertyInformation);

        foreach ($propertiesInformations as $propertyInformation) {
            foreach ($this->loadPropertyAnnotationForPropertyWithNameAndEntityPropertiesWithClassName($propertyInformation->name, $propertiesClassName) as $annotationName => $annotationValue) {
                if (\is_null($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::PROPERTY_SCOPE))) return;
                $annotation = new $annotationClass($propertyInformation, $entityInformation);
                $annotation->process($annotationValue);
                $propertyInformation->annotations[] = $annotation;
            }
        }

        foreach ($propertiesInformations as $propertyInformation) $entityInformation->addPropertyInformation ($propertyInformation);

        return $entityInformation;
    }

    /**
     * @param string $entityClassName
     * @throws \obo\Exceptions\Exception
     */
    protected function validateEntityWithClassName($entityClassName) {

        foreach ($this->entitiesInformations[$entityClassName]->annotations as $annotation) $annotation->validate($this);

        foreach ($this->entitiesInformations[$entityClassName]->propertiesInformation as $propertyInformation) {
            foreach ($propertyInformation->annotations as $annotation) $annotation->validate($this);
        }

        foreach ($this->entitiesInformations[$entityClassName]->propertiesInformation as $propertyInformation) {
            if (\is_null($propertyInformation->dataType) AND (!is_null($propertyInformation->varName))) {
                throw new \obo\Exceptions\Exception("Property with name '{$propertyInformation->name}' in entity '{$entityClassName}' have not set data type");
            }
        }
    }

    /**
     * @param string $entityClassName
     * @return array
     */
    protected function loadEntityAnnotationForEntityWithClassName($entityClassName) {
        $annotations = [];

        foreach($this->ancestorsForClassWithName($entityClassName) as $class) {
            $classAnnotations = [];

            foreach($class::getReflection()->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !==0) continue;
                $classAnnotations[$annotationName] = (array) $annotationValue[0];
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
        $annotations = [];

        foreach($this->ancestorsForClassWithName($entityClassName) as $class) {
            if (!$class::getReflection()->hasMethod($methodName)) continue;
            $classAnnotations = [];

            foreach($class::getReflection()->getMethod($methodName)->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !==0) continue;
                $classAnnotations[$annotationName] = (array) $annotationValue[0];
            }

            $annotations = array_replace_recursive($annotations, $classAnnotations);
        }

        return $annotations;
    }

    /**
     * @param string $propertyName
     * @param string $entityPropertiesClassName
     * @return type
     */
    protected function loadPropertyAnnotationForPropertyWithNameAndEntityPropertiesWithClassName($propertyName, $entityPropertiesClassName) {
        $annotations = [];

        foreach($this->ancestorsForClassWithName($entityPropertiesClassName) as $class) {
            if (!$class::getReflection()->hasProperty($propertyName)) continue;
            $classAnnotations = [];

            foreach($class::getReflection()->getProperty($propertyName)->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !==0) continue;
                $classAnnotations[$annotationName] = (array) $annotationValue[0];
            }

            $annotations = array_replace_recursive($annotations, $classAnnotations);
        }

        return $annotations;
    }

    /**
     * @param string $entityClassName
     * @return string
     * @throws \obo\Exceptions\DefinitionException
     */
    protected function managerClassNameForEntityWithClassName($entityClassName) {
        if (\class_exists($propertiesClassName = $entityClassName . "Manager")) return $propertiesClassName;
        throw new \obo\Exceptions\DefinitionException("Manager class for entity with name '{$entityClassName}' does not exist");
    }

    /**
     * @param string $entityClassName
     * @return string
     * @throws \obo\Exceptions\DefinitionException
     */
    protected function propertiesClassNameForEntityWithClassName($entityClassName) {
        if (\class_exists($propertiesClassName = $entityClassName . "Properties")) return $propertiesClassName;
        throw new \obo\Exceptions\DefinitionException("Properties class for entity with name '{$entityClassName}' does not exist");
    }

    /**
     * @param string $className
     * @return string
     */
    protected function defaultRepositoryNameForEntityWithClassName($className) {
        $repositoryName = "";
        foreach (\explode("\\", $className) as $namePart) $repositoryName .= \ucfirst($namePart);
        return $repositoryName;
    }

    /**
     * @param string $propertyName
     * @param string $entityClassName
     * @return type
     */
    protected function defaultColumnNamePropertiesForPropertyWithName($propertyName, $entityClassName) {
        return $propertyName;
    }

    /**
     * @param string $entityClassName
     * @return string
     */
    protected function defaultPrimaryPropertyNameForEntityWithClassName($entityClassName) {
        return "id";
    }

    /**
     * @param string $propertyName
     * @param string $entityClassName
     * @return boolean
     */
    protected function defaultAutoIncrementValueForPropertyWithName($propertyName, $entityClassName) {
        return false;
    }

    /**
     * @param string $propertyName
     * @param string $entityClassName
     * @return boolean
     */
    protected function defaultNullableValueForPropertyWithName($propertyName, $entityClassName) {
        return true;
    }

    /**
     *
     * @param string $propertyName
     * @param string $entityClassName
     * @return boolean
     */
    protected function defaultPersistableValuePropertiesForPropertyWithName($propertyName, $entityClassName) {
        return true;
    }

    /**
     *
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return type
     */
    protected function defaultDataTypeForProperty(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        if (\is_null($propertyInformation->varName)) return null;

        if ($propertyInformation->defaultValue === false OR $propertyInformation->defaultValue === true) {
            return \obo\DataType\Factory::createDataTypeBoolean($propertyInformation);
        } elseif (\is_numeric($propertyInformation->defaultValue) AND \is_int($propertyInformation->defaultValue * 1)) {
            return \obo\DataType\Factory::createDataTypeInteger($propertyInformation);
        } elseif (\is_numeric($propertyInformation->defaultValue) AND \is_float($propertyInformation->defaultValue * 1)) {
            return \obo\DataType\Factory::createDataTypeFloat($propertyInformation);
        } elseif (is_string($propertyInformation->defaultValue)) {
            return \obo\DataType\Factory::createDataTypeString($propertyInformation);
        } elseif (\is_array($propertyInformation->defaultValue)) {
            return \obo\DataType\Factory::createDataTypeArray($propertyInformation);
        } else {
            return null;
        }
    }

    /**
     * @param string $code
     * @return string
     */
    protected function findClasses($code) {
        $classes = [];
        $count = count($tokens = token_get_all($code));
        $namespace = "";

        for($i = 0; $i < $count; $i++) {
            if($tokens[$i][0] === T_NAMESPACE AND $i++) {
                $namespace = "";
                while($tokens[++$i][0] === T_STRING OR $tokens[$i][0] === T_NS_SEPARATOR) $namespace .= $tokens[$i][1];
            } elseif ($tokens[$i][0] === T_CLASS) {
                $classes[] = $namespace . "\\" . $tokens[$i += 2][1];
            }
        }

        return $classes;
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
 }
