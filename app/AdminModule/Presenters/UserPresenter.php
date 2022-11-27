<?php

namespace App\AdminModule\Presenters;

use App\Model\Facades\UsersFacade;

/**
 * Class UserPresenter
 * @package App\AdminModule\Presenters
 */

class UserPresenter extends BasePresenter {
    private UsersFacade $usersFacade;

    public function renderDefault(): void {
        $this->template->users = $this->usersFacade->findUsersWithoutUser($this->user);
    }

    public function actionDelete(int $id): void {
        try {
            $user = $this->usersFacade->getUser($id);
        } catch (\Exception $e) {
            $this->flashMessage('Požadovaný uživatel nebyl nalezen.', 'error');
            $this->redirect('default');
        }

        if (!$this->user->isAllowed($user,'delete')) {
            $this->flashMessage('Tohoto uživatele není možné smazat.', 'error');
            $this->redirect('default');
        }

        if ($this->usersFacade->deleteUser($user)) {
            $this->flashMessage('Uživatel byl smazán.');
        } else {
            $this->flashMessage('Tohoto uživatele není možné smazat.', 'error');
        }

        $this->redirect('default');
    }

    public function handleToggleAdmin(int $id): void {
        try {
            $user = $this->usersFacade->getUser($id);
        } catch (\Exception $e) {
            $this->flashMessage('Požadovaný uživatel nebyl nalezen.', 'error');
            $this->redirect('default');
        }

        if (!$this->user->isAllowed($user,'toggleAdmin')) {
            $this->flashMessage('Nemáte oprávnění k změně administrátorských práv tohoto uživatele.', 'error');
            $this->redirect('default');
        }

        $this->usersFacade->toggleAdmin($user);
        $this->redirect('default');
    }

    #region injections
    public function injectUsersFacade(UsersFacade $usersFacade): void {
        $this->usersFacade = $usersFacade;
    }
    #endregion
}