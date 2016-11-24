<?php
namespace Dravencms\FrontModule\Components\Discussion\Discussion;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Dravencms\Components\BaseControl;
use Dravencms\Components\BaseFormFactory;
use Nette\Application\UI\Form;
use Nette\Application\Responses\JsonResponse;

/**
 * Description of Discussions
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class Detail extends BaseControl
{
    /** @inject @var \Model\Discussions\Discussions * */
    public $discussionsModel;

    /** @inject @var \Model\Config\Config * */
    public $config;

    /** @var BaseFormFactory @inject */
    public $baseFormFactory;

    public function render($id)
    {
        $detail = $this->discussionsModel->where('id', $id)
            ->where('active', 1)->fetch();
    }

    public function createComponentNewPostForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addHidden('discussionsId');
        $form->addHidden('discussionsPostsId');


        $form->addText('name')
            ->setRequired('Zadejte prosím jméno.');

        $form->addTextArea('text')
            ->setRequired('Zadejte prosím text.');

        $form->addReCaptcha();

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'newPostFormValidate'];
        $form->onSuccess[] = [$this, 'newPostFormSuccess'];


        return $form;
    }

    public function newPostFormValidate(Form $form)
    {
    }

    public function newPostFormSuccess(Form $form)
    {
        $values = $form->getValues();

        $values->text = strip_tags($values->text, '<p><a><img><strong>');

        if (!$values->discussionsPostsId) {
            $values->discussionsPostsId = null;
        }

        $values->created = new \DateTime;
        $values->useragent = $_SERVER['HTTP_USER_AGENT'];
        $values->ip = $this->getRequest()->getRemoteAddress();
        $this->discussionsModel->get($values->discussionsId)->related('discussionsPosts')->insert($values);

        $this->redirect('this');
    }

    public function handlegetDiscussion($id)
    {
        $detail = $this->discussionsModel->where('active', 1)->fetch()
            ->related('discussionsPosts')
            ->where('id', $id)->fetch();

        if ($detail) {

            $detail = $detail->toArray();
            $detail['created'] = $detail['created']->format('d.m.Y H:i:s');
        } else {
            $detail = array();
        }
        $this->sendResponse(new JsonResponse($detail));
    }
}
