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

    const NAME_ANNOTATION = "name";

    /**
     * @var array
     */
    protected $annotationsDefinitions = [];

    /**
     * @var array
     */
    protected $dataTypes = [];

    /**
     * @var \obo\Carriers\EntityInformationCarrier[]
     */
    protected $entitiesInformations = [];

    /**
     * @var \obo\Carriers\EntityInformationCarrier[]
     */
    protected $entitiesInformationsByClassNames = [];

    /**
     * @var \obo\Carriers\EntityInformationCarrier[]
     */
    protected $entitiesInformationsByEntitiesNames = [];

    /**
     * @return \obo\Carriers\EntityInformationCarrier[]
     */
    public function entitiesInformations() {
        return $this->entitiesInformations;
    }

    /**
     * @return \obo\Carriers\EntityInformationCarrier[]
     */
    public function entitiesInformationsByClassNames() {
        return $this->entitiesInformationsByClassNames;
    }

    /**
     * @return \obo\Carriers\EntityInformationCarrier[]
     */
    public function entitiesInformationsByEntitiesNames() {
        return $this->entitiesInformationsByEntitiesNames;
    }

    /**
     * @param string $annotationClassName
     * @param bool $forced
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function registerAnnotation($annotationClassName, $forced = false) {
        if (!$forced AND $this->existAnnotationWithNameForScope($annotationClassName::name(), $annotationClassName::scope())) throw new \obo\Exceptions\Exception ("Can't register annotation with name " . $annotationClassName::name() . " for scope " . $annotationClassName::scope() . ", is already registered");
        $this->annotationsDefinitions[$annotationClassName::scope() . "-obo-" . $annotationClassName::name()] = $annotationClassName;
    }

    /**
     * @param string $annotationClassName
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function unregisterAnnotation($annotationClassName) {
        if (!$this->existAnnotationWithNameForScope($annotationClassName::name(), $annotationClassName::scope())) throw new \obo\Exceptions\Exception ("Can't unregister annotation with name " . $annotationClassName::name() . " for scope " . $annotationClassName::scope() . ", is not registered");
        unset($this->annotationsDefinitions[$annotationClassName::scope() . "-obo-" . $annotationClassName::name()]);
    }

    /**
     * @param string $annotationName
     * @param string $scope
     * @return bool
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function existAnnotationWithNameForScope($annotationName, $scope) {
        if (\strpos($annotationName, self::ANNOTATION_PREFIX) !== 0) return false;
        if (isset($this->annotationsDefinitions["{$scope}-{$annotationName}"])) return true;
        throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$annotationName}' for {$scope} does not exist");
    }

    /**
     * @param string $annotationName
     * @param string $scope
     * @return string
     */
    public function annotationClassWithNameForScope($annotationName, $scope) {
        return $this->existAnnotationWithNameForScope($annotationName, $scope) ? $this->annotationsDefinitions["{$scope}-{$annotationName}"] : null;
    }

    /**
     * @param string $dataTypeClassName
     * @param bool $forced
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function registerDatatype($dataTypeClassName, $forced = false) {
        if (!$forced AND isset($this->dataTypes[$dataTypeClassName::name()])) throw new \obo\Exceptions\Exception ("Can't register dataType with name " . $dataTypeClassName::name() .", dataType is already registered");
        if (!\is_subclass_of($dataTypeClassName, "\obo\Interfaces\IDataType")) throw new \obo\Exceptions\Exception("Can't register dataType with name " . $dataTypeClassName::name() .", dataType does not implement interface '\obo\Interfaces\IDataType'");
        $this->dataTypes[$dataTypeClassName::name()] = $dataTypeClassName;
    }

    /**
     * @param string $dataTypeClassName
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function unregisterDatatype($dataTypeClassName) {
        if (!isset($this->dataTypes[$dataTypeClassName::name()])) throw new \obo\Exceptions\Exception ("Can't unregister dataType with name " . $dataTypeClassName::name() .", dataType isn't registered");
        unset($this->dataTypes[$dataTypeClassName::name()]);
    }

    /**
     * @param string $name
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param array $options
     * @return \obo\Interfaces\IDataType
     * @throws \obo\Exceptions\Exception
     */
    public function createDatatype($name, \obo\Carriers\PropertyInformationCarrier $propertyInformation, array $options = []) {
        if (!isset($this->dataTypes[$name])) throw new \obo\Exceptions\Exception ("Can't create dataType with name " . $name .", dataType isn't registered");
        $dataTypeClassName = $this->dataTypes[$name];
        return $dataTypeClassName::createDatatype($propertyInformation, $options);
    }

    /**
     * @param array $dirPaths
     * @return \obo\Carriers\EntityInformationCarrier[]
     */
    public function analyze(array $dirPaths) {
        $entitiesClasses = [];
        $entities = [];

        foreach ($dirPaths as $dirPath) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath), \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);
            $files = new \RegexIterator($iterator, '#^.+\.php$#', \RegexIterator::MATCH, \RegexIterator::USE_KEY);

            foreach ($files as $fileName) {
                foreach (self::findClasses(file_get_contents($fileName)) as $className) {
                    $reflection = new \ReflectionClass($className);
                    if ($reflection->isSubclassOf("\\obo\\Entity")) $entitiesClasses[] = $className;
                }
            }
        }

        foreach ($entitiesClasses as $entityClassName) {
            $this->entitiesInformationsByClassNames[$entityClassName] = $this->entitiesInformations[] = $entityInformation = $this->analyzeEntityWithClassName($entityClassName);
            if (!isset($entities[$entityInformation->name]["__leaves"])) $entities[$entityInformation->name]["__leaves"] = [];

            if (!isset($entities[$entityInformation->name][$entityClassName])) {
                $entities[$entityInformation->name][$entityClassName] = ["className" => $entityClassName, "childs" => []];
                if (!$entityInformation->isAbstract)$entities[$entityInformation->name]["__leaves"][$entityClassName] = &$entities[$entityInformation->name][$entityClassName];
            }

            if (!isset($entities[$entityInformation->name][$entityInformation->parentClassName])) {
                $entities[$entityInformation->name][$entityInformation->parentClassName] = ["className" => $entityInformation->parentClassName, "childs" => &$entities[$entityInformation->name][$entityClassName]];
            } else {
                $entities[$entityInformation->name][$entityInformation->parentClassName]["childs"][] = &$entities[$entityInformation->name][$entityClassName];
                unset($entities[$entityInformation->name]["__leaves"][$entityInformation->parentClassName]);
            }
        }

        foreach ($entities as $entityName => $entity) {
            if (count($entity["__leaves"]) > 1) throw new \obo\Exceptions\Exception("Unable to resolve class for entity with name '{$entityName}' because more then one class exists which could be used (" . \implode(", ", \array_keys($entity["__leaves"])) . ")");
            if (count($entity["__leaves"]) !== 0) $this->entitiesInformationsByEntitiesNames[$entityName] = $this->entitiesInformationsByClassNames[\current($entity["__leaves"])["className"]];
        }

        foreach ($entitiesClasses as $entityClassName) {
            $this->validateEntityWithClassName($entityClassName);
        }

        foreach ($entitiesClasses as $entityClassName) {
            $this->finalizeEntityWithClassName($entityClassName);
        }

        return $this->entitiesInformationsByClassNames;
    }

    /**
     * @param string $entityClassName
     * @return string
     */
    protected function getEntityNameForClass($entityClassName) {
        $classAnnotations = $this->loadEntityAnnotationForEntityWithClassName($entityClassName);
        $entityName = $entityClassName;

        foreach ($classAnnotations as $annotationName => $annotationValue) {
            if ($annotationName === self::ANNOTATION_PREFIX . self::NAME_ANNOTATION) {
                $entityName = $annotationValue[0];
            }
        }

        return $entityName;
    }

    /**
     * @param string $entityClassName
     * @return string
     */
    protected function loadPropertiesOwnerEntityHistoryMap($entityClassName) {
        $lastEntityName = null;
        $classes = $this->ancestorsForClassWithName($entityClassName);
        $propertiesArray = [];

        foreach ($classes as $className) {
            $propertyClass = $this->propertiesClassNameForEntityWithClassName($className);
            $classReflection = $propertyClass::getReflection();
            $currentEntityName = $this->getEntityNameForClass($className);

            foreach ($classReflection->getProperties() as $propertyReflection) {
                if ($lastEntityName !== $currentEntityName OR !isset($propertiesArray[$propertyReflection->name]) OR !array_key_exists($propertyReflection->name, $propertiesArray)) {
                    $propertiesArray[$propertyReflection->name][$className] = $currentEntityName;
                }
            }

            $lastEntityName = $currentEntityName;
        }
        return $propertiesArray;
    }

    /**
     * @param string $entityClassName
     * @return \obo\Carriers\EntityInformationCarrier
     */
    protected function analyzeEntityWithClassName($entityClassName) {
        $entityClassReflection = $entityClassName::getReflection();
        $entityInformation = new \obo\Carriers\EntityInformationCarrier();
        $entityInformation->className = $entityClassName;
        $entityInformation->namespace = $entityClassReflection->getNamespaceName();
        $entityInformation->name = $this->defaultNameForEntityWithClassName($entityClassName);
        $entityInformation->parentClassName = $entityClassReflection->parentClass->name;
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
            if (($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::ENTITY_SCOPE)) === null) continue;
            $annotation = new $annotationClass($entityInformation);
            $annotation->process($annotationValue);
            $entityInformation->annotations[] = $annotation;
        }

        foreach ($entityClassReflection->getMethods() as $method) {
            foreach ($this->loadMethodAnnotationForMethodWithNameAndEntityWithClassName($method->name, $entityClassName) as $annotationName => $annotationValue) {
                if (($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::METHOD_SCOPE)) === null) continue;
                $annotation = new $annotationClass($entityInformation, $method->name);
                $annotation->process($annotationValue);
                $entityInformation->annotations[] = $annotation;
            }
        }

        $propertiesInformations = [];
        $propertiesMethodAccess = [];
        $classVariables = \get_class_vars($entityInformation->propertiesClassName);

        foreach ($propertiesClassReflection->getMethods() as $method) {
            $methodName = $method->name;

            if ($method->class === "obo\\EntityProperties" OR $method->class === "obo\\Object") continue;

            if (!\preg_match("#^((get)|(set))[A-Z].+#", $methodName)) continue;

            $propertyName = lcfirst(\preg_replace("#^((get)|(set))#", "", $methodName));

            $propertiesMethodAccess[$propertyName][\preg_match("#^get[A-Z].+#", $methodName) ? "getterName" : "setterName"] = $methodName;
        }

        $propertiesOwnerEntityHistoryMap = $this->loadPropertiesOwnerEntityHistoryMap($entityClassName);

        foreach ($propertiesClassReflection->getProperties() as $property) {
            if (!$property->isPublic() OR $property->class === "obo\\EntityProperties" OR $property->class === "obo\\Object") continue;

            $propertyInformation = new \obo\Carriers\PropertyInformationCarrier();
            $propertyInformation->name = $property->name;
            $propertyInformation->varName = $property->name;
            $propertyInformation->columnName = $this->defaultColumnNamePropertiesForPropertyWithName($property->name, $entityClassName);
            $propertyInformation->persistable = $this->defaultPersistableValuePropertiesForPropertyWithName($property->name, $entityClassName);
            $propertyInformation->autoIncrement = $this->defaultAutoIncrementValueForPropertyWithName($property->name, $entityClassName);
            $propertyInformation->nullable = $this->defaultNullableValueForPropertyWithName($property->name, $entityClassName);
            $propertyInformation->ownerEntityHistory = $propertiesOwnerEntityHistoryMap[$property->name];

            if (isset($classVariables[$property->name]) OR \array_key_exists($property->name, $classVariables)) $propertyInformation->defaultValue = $classVariables[$property->name];

            if (isset($propertiesMethodAccess[$property->name])) {

                foreach ($propertiesMethodAccess[$property->name] as $methodType => $methodName) {
                    $propertyInformation->$methodType = $methodName;
                    $propertyInformation->{$methodType === "getterName" ? "directAccessToRead" : "directAccessToWrite"} = false;
                }

                unset($propertiesMethodAccess[$property->name]);
            }

            $propertiesInformations[$propertyInformation->name] = $propertyInformation;
        }

        foreach ($propertiesMethodAccess as $propertyName => $propertyAccess) {
            $propertyInformation = new \obo\Carriers\PropertyInformationCarrier();
            $propertyInformation->name = $propertyName;
            foreach ($propertyAccess as $methodType => $methodName) $propertyInformation->$methodType = $methodName;
            $propertiesInformations[$propertyInformation->name] = $propertyInformation;
        }

        foreach ($propertiesInformations as $propertyInformation) $propertyInformation->dataType = $this->defaultDataTypeForProperty($propertyInformation);

        foreach ($propertiesInformations as $propertyInformation) {
            foreach ($this->loadPropertyAnnotationForPropertyWithNameAndEntityPropertiesWithClassName($propertyInformation->name, $propertiesClassName) as $annotationName => $annotationValue) {
                if (($annotationClass = $this->annotationClassWithNameForScope($annotationName, \obo\Annotation\Base\Definition::PROPERTY_SCOPE)) === null) return;
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
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    protected function validateEntityWithClassName($entityClassName) {
        if ($this->entitiesInformationsByClassNames[$entityClassName]->isAbstract) return;
        foreach ($this->entitiesInformationsByClassNames[$entityClassName]->annotations as $annotation) $annotation->validate($this);

        foreach ($this->entitiesInformationsByClassNames[$entityClassName]->propertiesInformation as $propertyInformation) {
            foreach ($propertyInformation->annotations as $annotation) $annotation->validate($this);
        }
    }

    /**
     * @param string $entityClassName
     * @return void
     */
    protected function finalizeEntityWithClassName($entityClassName) {
        if ($this->entitiesInformationsByClassNames[$entityClassName]->isAbstract) return;
        foreach ($this->entitiesInformationsByClassNames[$entityClassName]->annotations as $annotation) $annotation->finalize($this);

        foreach ($this->entitiesInformationsByClassNames[$entityClassName]->propertiesInformation as $propertyInformation) {
            foreach ($propertyInformation->annotations as $annotation) $annotation->finalize($this);
        }

        foreach ($this->entitiesInformationsByClassNames[$entityClassName]->propertiesInformation as $propertyInformation) {
            if ($propertyInformation->dataType === null AND ($propertyInformation->varName !== "")) {
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

        foreach ($this->ancestorsForClassWithName($entityClassName) as $class) {
            $classAnnotations = [];

            foreach ($class::getReflection()->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !== 0) continue;
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

        foreach ($this->ancestorsForClassWithName($entityClassName) as $class) {
            if (!$class::getReflection()->hasMethod($methodName)) continue;
            $classAnnotations = [];

            foreach ($class::getReflection()->getMethod($methodName)->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !== 0) continue;
                $classAnnotations[$annotationName] = (array) $annotationValue[0];
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
        $annotations = [];

        foreach ($this->ancestorsForClassWithName($entityPropertiesClassName) as $class) {
            if (!$class::getReflection()->hasProperty($propertyName)) continue;
            $classAnnotations = [];

            foreach ($class::getReflection()->getProperty($propertyName)->getAnnotations() as $annotationName => $annotationValue) {
                if (\strpos($annotationName, self::ANNOTATION_PREFIX) !== 0) continue;
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
    protected function defaultNameForEntityWithClassName($className) {
        return $className;
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
     * @return string
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
     * @return bool
     */
    protected function defaultAutoIncrementValueForPropertyWithName($propertyName, $entityClassName) {
        return false;
    }

    /**
     * @param string $propertyName
     * @param string $entityClassName
     * @return bool
     */
    protected function defaultNullableValueForPropertyWithName($propertyName, $entityClassName) {
        return true;
    }

    /**
     *
     * @param string $propertyName
     * @param string $entityClassName
     * @return bool
     */
    protected function defaultPersistableValuePropertiesForPropertyWithName($propertyName, $entityClassName) {
        return true;
    }

    /**
     *
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\Base\DataType
     */
    protected function defaultDataTypeForProperty(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        if ($propertyInformation->varName === "") return null;

        if ($propertyInformation->defaultValue === false OR $propertyInformation->defaultValue === true) {
            return $this->createDatatype(\obo\DataType\BooleanDataType::name(), $propertyInformation);
        } elseif (\is_numeric($propertyInformation->defaultValue) AND \is_int($propertyInformation->defaultValue * 1)) {
            return $this->createDatatype(\obo\DataType\IntegerDataType::name(), $propertyInformation);
        } elseif (\is_numeric($propertyInformation->defaultValue) AND \is_float($propertyInformation->defaultValue * 1)) {
            return $this->createDatatype(\obo\DataType\FloatDataType::name(), $propertyInformation);
        } elseif (is_string($propertyInformation->defaultValue)) {
            return $this->createDatatype(\obo\DataType\StringDataType::name(), $propertyInformation);
        } elseif (\is_array($propertyInformation->defaultValue)) {
            return $this->createDatatype(\obo\DataType\ArrayDataType::name(), $propertyInformation);
        } else {
            return null;
        }
    }

    /**
     * @param string $code
     * @return string[]
     */
    public static function findClasses($code) {
        $classes = [];
        $count = count($tokens = token_get_all($code));
        $namespace = "";

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE AND $i++) {
                $namespace = "";
                while ($tokens[++$i][0] === T_STRING OR $tokens[$i][0] === T_NS_SEPARATOR)$namespace .= $tokens[$i][1];
            } elseif ($tokens[$i][0] === T_CLASS AND $tokens[$i - 1][0] !== T_DOUBLE_COLON) {
                $classes[] = ($namespace ? $namespace . "\\" : "") . $tokens[$i += 2][1];
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
            \array_unshift($classes, $class);
        }

        return $classes;
    }

}
