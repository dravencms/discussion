<?php
/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Discussion\PostForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Discussion\Entities\Discussion;
use Dravencms\Model\Discussion\Entities\Post;
use Dravencms\Model\Discussion\Repository\PostRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Nette\Http\Request;

/**
 * Description of PostForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class PostForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var PostRepository */
    private $postRepository;

    /** @var Discussion */
    private $discussion;

    /** @var Request  */
    private $request;

    /** @var Post|null */
    private $post = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * PostForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param PostRepository $postRepository
     * @param Request $request
     * @param Discussion $discussion
     * @param Post|null $post
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        PostRepository $postRepository,
        Request $request,
        Discussion $discussion,
        Post $post = null
    ) {
        parent::__construct();

        $this->post = $post;
        $this->discussion = $discussion;

        $this->request = $request;
        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->postRepository = $postRepository;


        if ($this->post) {
            $defaults = [
                'name' => $this->post->getName(),
                'isShowName' => $this->post->isShowName(),
                'isActive' => $this->post->isActive(),
            ];

        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return \Dravencms\Components\BaseForm
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addText('name')
            ->setRequired('Please enter name.');

        $form->addText('email')
            ->setRequired('Please enter email.')
            ->addRule(Form::EMAIL, 'Please Enter valid email');

        $form->addText('title')
            ->setRequired('Please enter title.');

        $form->addTextarea('text');
        
        $form->addCheckbox('isActive');
        $form->addCheckbox('isShowName');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        if (!$this->presenter->isAllowed('discussion', 'edit')) {
            $form->addError('Nemáte oprávění editovat post.');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        $ip = $this->request->getRemoteAddress();
        $userAgent = $this->request->getHeader('User-Agent');

        if ($this->post) {
            $post = $this->post;
            $post->setName($values->name);
            $post->setEmail($values->email);
            $post->setTitle($values->title);
            $post->setText($values->text);
            $post->setIp($ip);
            $post->setUserAgent($userAgent);
        } else {
            $post = new Post($this->discussion, $values->name, $values->email, $values->title, $values->text, $ip, $userAgent);
        }


        $this->entityManager->persist($post);

        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/PostForm.latte');
        $template->render();
    }
}