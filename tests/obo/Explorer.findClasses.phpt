<?php

/**
 * Test: Explorer - find class names in PHP code
 */

use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

test(function() {
    $code = <<<'EOD'
<?php

/**
 * This file is part of demo application for example of using framework Obo beta 2 version (http://www.obophp.org/)
 * Created under supervision of company as CreatApps (http://www.creatapps.cz/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

    namespace Notice;

# A class defining the entity is usually better to split into its own file. Here are clarity placed in one file

# definition of properties

    class NoticeProperties extends \Base\EntityProperties {
        /** @obo-one(targetEntity = "\Users\User")*/
        public $user;
        public $text = "";
        /**
         * @obo-timeStamp(beforeInsert)
         * @obo-dataType(dateTime)
         */
        public $dateTimeInserted = "";
        public $deleted = false;
    }

# definition entity

    /**
     * @obo-softDeletable
     * @property \Users\User $user
     * @property string $text
     * @property string $dateTimeInserted
     */
    class Notice extends \Base\Entity{

        const SOME_CONSTANT = 5;

        public function someMethod() {
            return \Notice\Notice::SOME_CONSTANT;
        }

        public function getClassName() {
            return \Notice\Notice::class;
        }

        /**
         * @param \Nette\Forms\Form $form
         * @return \Nette\Forms\Form
         */
        public static function constructForm(\Nette\Forms\Form $form) {
            $form->addHidden('id');
            $form->addText('text', 'Text notice', 50);
            return $form;
        }
    }

# definition entity manager

    class NoticeManager extends \Base\EntityManager{

        /**
         * @param null|int|array $specification
         * @return \Notice\Notice
         */
        public static function notice($specification) {
            return self::entity($specification);
        }

        /**
         * @param \obo\Interfaces\IPaginator $paginator
         * @param \obo\Interfaces\IFilter $filter
         * @return \Notice\Notice[]
         */
        public static function noticesForUser(\Users\User $user,\obo\Interfaces\IPaginator $paginator = null, \obo\Interfaces\IFilter $filter = null) {
            return self::findEntities(\obo\Carriers\QueryCarrier::instance()->where("AND {user} = %i", $user->id), $paginator, $filter);
        }

        /**
         * @param \Nette\Forms\Form $form
         * @return \Nette\Forms\Form | \Notice\Notice
         */
        public static function newNoticeFromForm(\Nette\Forms\Form $form) {
            return self::newEntityFromForm(\Notice\Notice::constructForm($form));
        }

        /**
         * @param \Nette\Forms\Form $form
         * @param \Notice\Notice $notice
         * @return \Nette\Forms\Form | \Notice\Notice
         */
        public static function editNoticeFromForm(\Nette\Forms\Form $form, \Notice\Notice $notice = null) {
            return self::editEntityFromForm(\Notice\Notice::constructForm($form), $notice);
        }
    }
EOD;

    Assert::same(['Notice\NoticeProperties', 'Notice\Notice', 'Notice\NoticeManager'], \obo\Services\EntitiesInformation\Explorer::findClasses($code));
});
