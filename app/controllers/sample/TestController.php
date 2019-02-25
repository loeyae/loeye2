<?php

/**
 * WechatController.php
 *
 * Licensed under the Apache License, Version 2.0 (the "License"),
 * see LICENSE for more details: http://www.apache.org/licenses/LICENSE-2.0.
 *
 * @author  Zhang Yi <loeyae@gmail.com>
 * @version SVN: $Id: Zhang Yi $
 */
namespace app\controllers\sample;

use loeye\std\Controller;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Translation as I18n;

/**
 * TestController
 *
 * @author   Zhang Yi <loeyae@gmail.com>
 */
class TestController extends Controller {

    protected $cache;

    public function prepare() {
        parent::prepare();
        return true;
    }

    /**
     * IndexAction
     *
     * @return void
     */
    public function IndexAction() {
        $output = [$this->context->getAppConfig()->getSetting('constants')];
        $config = new \loeye\base\Configuration('validate', 'sample');
        $output[] = $config->getConfig(null, 'validate=content_create');
        $this->context->set('test.output', $output);
        $this->view = ['tpl' => 'sample.index.tpl'];
    }

    /**
     * ValidateAction
     *
     * @return void
     */
    public function ValidateAction() {
        $translator = new I18n\Translator('zh_CN');
        $loader = new I18n\Loader\XliffFileLoader();
        $resourseDir = PROJECT_DIR .'/../vendor/symfony/validator/Resources/translations/';
        foreach (new \FilesystemIterator($resourseDir, \FilesystemIterator::KEY_AS_FILENAME) as $key => $item) {
            if (!$item->isFile()) {
                continue;
            }
            $lpos = strpos($key, ".");
            $rpos = strrpos($key, ".");
            $locale = substr($key, $lpos + 1, $rpos - $lpos - 1);
            $translator->addResource('xlf', $item->getRealPath(), $locale);
        }
        $translator->addLoader('xlf', $loader);
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection(['fields' => [
            'id' => new Assert\Optional([new Assert\Regex(['pattern' => '/.*/'])]),
            'name' => new Assert\Required([
                    new Assert\NotNull(),
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 12]),
                ]),
            'array' => new Assert\Required([
                    new Assert\Type(['type' => 'array']),
                    new Assert\Collection(['fields' => [
                            new Assert\Optional([new Assert\Type(['type' => 'string']),
                            new Assert\Length(['max' => 12]),
                            ]),
                        ]
                    ])
                ]),
            ],
            'allowExtraFields' => true,
        ]);
        $result = $validator->validate(['id' => 0, 'name' => null, 'ccc' => 3, 'array' => [1]], $constraint);
        var_dump($result);
        echo '<pre>';
        for ($i = 0; $i < $result->count(); $i++) {
            var_dump($result->get($i));
            var_dump($result->get($i)->getPropertyPath());
            var_dump($translator->trans($result->get($i)->getMessageTemplate(), $result->get($i)->getParameters()));
        }
    }

    /**
     * ValidatorAction
     *
     * @return void
     */
    public function ValidatorAction() {
        $bundle = null;
        $validator = new \loeye\base\Validator($this->context->getAppConfig(), $bundle);
        $report = $validator->validate(['username' => '111', 'password' => '<script>alert(1)</script>adaf<div>aaa</div>', 'title' => '<script>alert(1)</script>adafaaa'], 'content_create');
        $this->context->set('test.output', $report);
        $this->view = ['tpl' => 'sample.index.tpl'];
    }

    /**
     * EntityAction
     *
     * @return void
     */
    public function EntityAction()
    {
        /**
        $user = $this->context->db()->entity(\app\models\entity\User::class, 1);
        $user->setName('test-001@'. time());
        $this->context->db()->save($user);
         */
        $user = $this->context->db()->one(\app\models\entity\User::class, ['name' => 'test-002']);
        $this->context->set('test.output', $user);
        $this->view = ['tpl' => 'sample.index.tpl'];
    }
    /**
     * SqlAction
     *
     * @return void
     */
    public function SqlAction()
    {
        $qb = $this->context->db()->createQueryBuilder()->select('u')->where('u.id = 1')->from(\app\models\entity\User::class, 'u');
        $query = $qb->getQuery();
        var_dump($query->getSQL());
        $user = $query->getResult();
        $this->context->set('test.output', $user);
        $this->view = ['tpl' => 'sample.index.tpl'];
    }

    /**
     * QueryAction
     *
     * @return void
     */
    public function QueryAction()
    {
        $user = $this->context->db()->query('SELECT * FROM user WHERE id = 1');
        $this->context->set('test.output', $user);
        $this->view = ['tpl' => 'sample.index.tpl'];
    }

    public function ApiAction()
    {
        $client = new \app\services\client\SampleClient();
        $user = $client->listUser();
        $this->context->set('test.output', $user);
        $this->view = ['tpl' => 'sample.index.tpl'];
    }

}
