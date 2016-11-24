<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\DiscussionModule;

use Dravencms\AdminModule\Components\Discussion\DiscussionForm\DiscussionFormFactory;
use Dravencms\AdminModule\Components\Discussion\DiscussionGrid\DiscussionGridFactory;
use Dravencms\AdminModule\Components\Discussion\PostForm\PostFormFactory;
use Dravencms\AdminModule\Components\Discussion\PostGrid\PostGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Discussion\Entities\Discussion;
use Dravencms\Model\Discussion\Entities\Post;
use Dravencms\Model\Discussion\Repository\DiscussionRepository;
use Dravencms\Model\Discussion\Repository\PostRepository;



/**
 * Description of DiscussionsPresenter
 *
 * @author Adam Schubert
 */
class DiscussionPresenter extends SecuredPresenter
{
    /** @var DiscussionRepository @inject */
    public $discussionRepository;

    /** @var PostRepository @inject */
    public $postRepository;

    /** @var DiscussionFormFactory @inject */
    public $discussionFormFactory;

    /** @var DiscussionGridFactory @inject */
    public $discussionGridFactory;

    /** @var PostFormFactory @inject */
    public $postFormFactory;

    /** @var PostGridFactory @inject */
    public $postGridFactory;

    /** @var null|Discussion */
    private $discussion = null;

    /** @var null|Post */
    private $post = null;

    /**
     * @isAllowed(discussion,edit)
     */
    public function renderDefault()
    {
        $this->template->h1 = 'Discussions';
    }

    /**
     * @isAllowed(discussion,edit)
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit($id)
    {
        if ($id)
        {
            $discussion = $this->discussionRepository->getOneById($id);
            if (!$discussion)
            {
                $this->error();
            }
            $this->discussion = $discussion;
            $this->template->h1 = 'Edit discussion';
        }
        else
        {
            $this->template->h1 = 'New discussion';
        }
    }

    /**
     * @isAllowed(discussion,edit)
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionPosts($id)
    {
        $discussion = $this->discussionRepository->getOneById($id);
        if (!$discussion)
        {
            $this->error();
        }
        $this->discussion = $discussion;
        $this->template->discussion = $discussion;
        $this->template->h1 = 'Discussion „' . $this->discussion->getName() . '“ posts.';
    }

    public function actionEditPost($discussionId, $postId = null)
    {
        $discussion = $this->discussionRepository->getOneById($discussionId);
        $this->discussion = $discussion;

        if ($postId)
        {
            $post = $this->postRepository->getOneById($postId);
            $this->post = $post;
        }

        $this->template->h1 = 'Discussion „' . $discussion->getName() . '“.';
    }

    /**
     * @return \AdminModule\Components\Discussion\PostGrid
     */
    public function createComponentGridPost()
    {
        $control = $this->postGridFactory->create($this->discussion);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Discussion post has been successfully deleted', 'alert-success');
            $this->redirect('Discussion:posts', $this->discussion->getId());
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Discussion\DiscussionGrid
     */
    public function createComponentGridDiscussion()
    {
        $control = $this->discussionGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Discussion has been successfully deleted', 'alert-success');
            $this->redirect('Discussion:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Discussion\DiscussionForm
     */
    public function createComponentFormDiscussion()
    {
        $control = $this->discussionFormFactory->create($this->discussion);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Discussion has been successfully saved', 'alert-success');
            $this->redirect('Discussion:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Discussion\PostForm
     */
    public function createComponentFormPost()
    {
        $control = $this->postFormFactory->create($this->discussion, $this->post);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Discussion post has been successfully saved', 'alert-success');
            $this->redirect('Discussion:posts', $this->discussion->getId());
        };
        return $control;
    }

}
