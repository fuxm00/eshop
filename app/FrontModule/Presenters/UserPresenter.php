<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\ForgottenPasswordForm\ForgottenPasswordForm;
use App\FrontModule\Components\ForgottenPasswordForm\ForgottenPasswordFormFactory;
use App\FrontModule\Components\NewPasswordForm\NewPasswordForm;
use App\FrontModule\Components\NewPasswordForm\NewPasswordFormFactory;
use App\FrontModule\Components\UserDetailsForm\UserDetailsForm;
use App\FrontModule\Components\UserDetailsForm\UserDetailsFormFactory;
use App\FrontModule\Components\UserLoginForm\UserLoginForm;
use App\FrontModule\Components\UserLoginForm\UserLoginFormFactory;
use App\FrontModule\Components\UserRegistrationForm\UserRegistrationForm;
use App\FrontModule\Components\UserRegistrationForm\UserRegistrationFormFactory;
use App\Model\Api\Facebook\FacebookApi;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\BadRequestException;

/**
 * Class UserPresenter - presenter pro akce týkající se uživatelů
 * @package App\FrontModule\Presenters
 * @property string $backlink
 */
class UserPresenter extends BasePresenter{

  /** @persistent */
  public string $backlink = '';

  private UsersFacade $usersFacade;
  private UserLoginFormFactory $userLoginFormFactory;
  private UserRegistrationFormFactory $userRegistrationFormFactory;
  private ForgottenPasswordFormFactory $forgottenPasswordFormFactory;
  private NewPasswordFormFactory $newPasswordFormFactory;
  private FacebookApi $facebookApi;
  private UserDetailsFormFactory $userDetailsFormFactory;
  
  /**
   * Akce pro odhlášení uživatele
   * @throws Nette\Application\AbortException
   */
  public function actionLogout():void {
    if ($this->user->isLoggedIn()){
      $this->user->logout();
    }
    $this->redirect('Product:list');
  }

  /**
   * Akce pro přihlášení - pokud už je uživatel přihlášen, tak ho jen přesměrujeme na homepage
   * @throws Nette\Application\AbortException
   */
  public function actionLogin():void {
    if ($this->user->isLoggedIn()){
      //obnovíme uložený požadavek - pokud se to nepovede, pokračujeme přesměrováním
      $this->restoreRequest($this->backlink);
      $this->redirect('Product:list');
    }
  }

  /**
   * Akce pro registraci - pokud už je uživatel přihlášen, tak ho jen přesměrujeme na homepage
   * @throws Nette\Application\AbortException
   */
  public function actionRegister():void {
    if ($this->user->isLoggedIn()){
      $this->redirect('Product:list');
    }
  }

  /**
   * Akce pro přihlášení pomocí Facebooku
   * @param bool $callback
   * @throws Nette\Application\AbortException
   * @throws Nette\Application\UI\InvalidLinkException
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
  public function actionFacebookLogin(bool $callback=false):void {
    if ($callback){
      #region návrat z Facebooku
      try{
        $facebookUser = $this->facebookApi->getFacebookUser(); //v proměnné $facebookUser máme facebookId, email a jméno uživatele => jdeme jej přihlásit

        //necháme si vytvořit identitu uživatele
        $userUdentity = $this->usersFacade->getFacebookUserIdentity($facebookUser);

        //přihlásíme uživatele
        $this->user->login($userUdentity);

      }catch (\Exception $e){
        $this->flashMessage('Přihlášení pomocí Facebooku se nezdařilo.','error');
        $this->redirect('Product:list');
      }

      //obnovíme uložený požadavek - pokud se to nepovede, pokračujeme přesměrováním
      $this->restoreRequest($this->backlink);
      $this->redirect('Product:list');
      #endregion návrat z Facebooku
    }else{
      #region přesměrování na přihlášení pomocí Facebooku
      $backlink = $this->link('//User:facebookLogin', ['callback'=>true]);
      $facebookLoginLink = $this->facebookApi->getLoginUrl($backlink);
      $this->redirectUrl($facebookLoginLink);
      #endregion přesměrování na přihlášení pomocí Facebooku
    }
  }
  
  /**
   * Akce pro zadání nového hesla v rámci jeho obnovy
   * @param int $user
   * @param string $code
   * @throws BadRequestException
   * @throws Nette\Application\AbortException
   */
  public function renderRenewPassword(int $user, string $code):void {
    if ($this->usersFacade->isValidForgottenPasswordCode($user, $code)){
      #region odkaz na obnovu hesla byl platný
      try{
        $userEntity=$this->usersFacade->getUser($user);
      }catch (\Exception $e){
        throw new BadRequestException('Požadovaný uživatel neexistuje.','error');
      }

      $form = $this->getComponent('newPasswordForm');
      $form->setDefaults($userEntity);
      #endregion odkaz na obnovu hesla byl platný
    }else{
      #region odkaz již není platný
      $this->flashMessage('Odkaz na změnu hesla již není platný. Pokud potřebujete heslo obnovit, zašlete žádost znovu.','error');
      $this->redirect('Product:list');
      #endregion odkaz již není platný
    }
  }

  /**
   * Formulář pro přihlášení existujícího uživatele
   * @return UserLoginForm
   */
  protected function createComponentUserLoginForm():UserLoginForm {
    $form=$this->userLoginFormFactory->create();
    $form->onFinished[]=function()use($form){
      $values=$form->getValues('array');
      try{
        $this->user->login($values['email'],$values['password']);
        //po přihlášení uživatele smažeme jeho kódy na obnovu hesla
        $this->usersFacade->deleteForgottenPasswordsByUser($this->user->id);
      }catch (\Exception $e){
        $this->flashMessage('Neplatná kombinace e-mailu a hesla!','error');
        $this->redirect('login');
      }

      //obnovíme uložený požadavek - pokud se to nepovede, pokračujeme přesměrováním
      $this->restoreRequest($this->backlink);
      $this->redirect('Product:list');
    };
    $form->onCancel[]=function()use($form){
      $this->redirect('Product:list');
    };
    return $form;
  }

  /**
   * Formulář pro registraci nového uživatele
   * @return UserRegistrationForm
   */
  protected function createComponentUserRegistrationForm():UserRegistrationForm {
    $form=$this->userRegistrationFormFactory->create();
    $form->onFinished[]=function()use($form){
      $values=$form->getValues('array');
      try{
        //po registraci uživatele rovnou i přihlásíme
        $this->user->login($values['email'],$values['password']);
        $this->flashMessage('Vítejte v našem eshopu :)');
      }catch (\Exception $e){
        $this->flashMessage('Při registraci se vyskytla chyba','error');
      }
      $this->redirect('Product:list');
    };
    $form->onCancel[]=function()use($form){
      $this->redirect('Product:list');
    };
    return $form;
  }

  /**
   * Formulář pro obnovu zapomenutého hesla
   * @return ForgottenPasswordForm
   */
  protected function createComponentForgottenPasswordForm():ForgottenPasswordForm {
    $form=$this->forgottenPasswordFormFactory->create();
    $form->onFinished[]=function($message=''){
      if (!empty($message)){
        $this->flashMessage($message);
      }
      $this->redirect('login');
    };
    $form->onCancel[]=function()use($form){
      $this->redirect('login');
    };
    return $form;
  }

  /**
   * Formulář pro zadání nového hesla
   * @return NewPasswordForm
   */
  protected function createComponentNewPasswordForm():NewPasswordForm {
    $form=$this->newPasswordFormFactory->create();
    $form->onFinished[]=function($message=''){
      if (!empty($message)){
        $this->flashMessage($message);
      }
      $this->redirect('login');
    };
    $form->onFailed[]=function($message=''){
      if (!empty($message)){
        $this->flashMessage($message);
      }
      $this->redirect('Product:list');
    };
    $form->onCancel[]=function()use($form){
      $this->redirect('Product:list');
    };
    return $form;
  }

  protected function createComponentUserDetailsForm(): UserDetailsForm {
    $form = $this->userDetailsFormFactory->create();
    $form->createSubcomponents();
    $form->prepareDefaults($this->user->getIdentity());
    $form->onSave[] = function () use($form) {
        $values = $form->getValues('array');
        try {
            $user = $this->usersFacade->getUserByEmail($this->user->getIdentity()->email);
        } catch (\Exception $e) {
            $this->flashMessage('Uživatel neexistuje', 'error');
            $this->redirect('User:profile');
        }
        $convertedValues = [
            "name" => $values["name"],
            "email" => $values["email"],
            "country" => empty($values['country']) ? null : $values['country'],
            "city" => empty($values['city']) ? null : $values['city'],
            "street" => empty($values['street']) ? null : $values['street'],
            "zip" => empty($values['zip']) ? null : $values['zip'],
            "telNumber" => empty($values['telNumber']) ? null : $values['telNumber'],
            "addressNumber" => empty($values['addressNumber']) ? null : $values['addressNumber'],
        ];

        $user->name = $convertedValues['name'];
        $user->email = $convertedValues['email'];
        $user->country = $convertedValues['country'];
        $user->city = $convertedValues['city'];
        $user->street = $convertedValues['street'];
        $user->zip = $convertedValues['zip'];
        $user->telNumber = $convertedValues['telNumber'];
        $user->addressNumber = $convertedValues['addressNumber'];

        try {
            $this->usersFacade->saveUser($user);
        } catch (\Exception $e) {
            $this->flashMessage('Nastala chyba při ukládání', 'error');
            $this->redirect('User:profile');
        }
        $this->user->identity->name = $convertedValues['name'];
        $this->user->identity->email = $convertedValues['email'];
        $this->user->identity->country = $convertedValues['country'];
        $this->user->identity->city = $convertedValues['city'];
        $this->user->identity->street = $convertedValues['street'];
        $this->user->identity->zip = $convertedValues['zip'];
        $this->user->identity->telNumber = $convertedValues['telNumber'];
        $this->user->identity->addressNumber = $convertedValues['addressNumber'];

        $this->user->refreshStorage();


        $this->flashMessage('Uživatelské údaje byly uloženy.');
        $this->redirect('User:profile');
    };

    return $form;
  }

  #region injections
  public function injectUsersFacade(UsersFacade $usersFacade):void {
    $this->usersFacade=$usersFacade;
  }

  public function injectUserLoginFormFactory(UserLoginFormFactory $userLoginFormFactory):void {
    $this->userLoginFormFactory=$userLoginFormFactory;
  }

  public function injectUserRegistrationFormFactory(UserRegistrationFormFactory $userRegistrationFormFactory):void {
    $this->userRegistrationFormFactory=$userRegistrationFormFactory;
  }

  public function injectForgottenPasswordFormFactory(ForgottenPasswordFormFactory $forgottenPasswordFormFactory):void {
    $this->forgottenPasswordFormFactory=$forgottenPasswordFormFactory;
  }

  public function injectNewPasswordFormFactory(NewPasswordFormFactory $newPasswordFormFactory):void {
    $this->newPasswordFormFactory=$newPasswordFormFactory;
  }

  public function injectFacebookApi( FacebookApi $facebookApi):void {
    $this->facebookApi=$facebookApi;
  }

  public function injectUserDetailsFormFactory(UserDetailsFormFactory $userDetailsFormFactory): void {
    $this->userDetailsFormFactory=$userDetailsFormFactory;
  }
  #endregion injections
}
