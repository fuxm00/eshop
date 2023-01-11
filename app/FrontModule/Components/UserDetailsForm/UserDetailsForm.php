<?php

namespace App\FrontModule\Components\UserDetailsForm;

use Nette\Security\IIdentity;
use Nette;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

class UserDetailsForm extends Form {
    use SmartObject;

    /** @var callable[] $onSave */
    public array $onSave = [];

    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null) {
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    }

    public function createSubcomponents(bool $isPurchaseOrder = false): void {
        $this->setHtmlAttribute('class', 'profile-form');

        $this->addText('name', 'Jméno a příjmení')
            ->setRequired('Zadejte prosím jméno')
            ->setMaxLength(40);

        $this->addEmail('email', 'E-mail')
            ->setRequired('Zadejte prosím e-mail')
            ->setMaxLength(255);

        $countryField = $this->addText('country', 'Země')
            ->setMaxLength(100);

        $cityField = $this->addText('city', 'Město')
            ->setMaxLength(100);

        $streetField = $this->addText('street', 'Ulice')
            ->setMaxLength(100);

        $addressNumberField = $this->addText('addressNumber', 'Číslo popisné')
            ->setMaxLength(10)
            ->addRule(Form::INTEGER, 'Číslo popisné musí být číslo');

        $zipField = $this->addText('zip', 'PSČ')
            ->addRule(
                Form::PATTERN,
                'PSČ musí být platné PSČ (s mezerou nebo bez)',
                '(^[0-9]{5}$)|(^[0-9]{3} [0-9]{2}$)'
            );

        $this->addText('telNumber', 'Telefonní číslo')
            ->setMaxLength(15)
            ->addRule(
                Form::PATTERN,
                'Telefonní číslo musí být číslo (bez mezer, může obsahovat předvolbu s + na začátku)',
                '^\+?[0-9+ ]+$'
            );

        if ($isPurchaseOrder) {
            $countryField->setRequired('Zadejte prosím zemi');
            $cityField->setRequired('Zadejte prosím město');
            $streetField->setRequired('Zadejte prosím ulici');
            $addressNumberField->setRequired('Zadejte prosím číslo popisné');
            $zipField->setRequired('Zadejte prosím PSČ');
        }

        $this->addSubmit('ok', $isPurchaseOrder ? 'Odeslat' : 'Uložit')
            ->setHtmlAttribute('class', 'btn green-btn')
            ->onClick[] = function () {
                $this->onSave($this);
            };
    }

    public function prepareDefaults(IIdentity $userIdentity): void {
        $this->setDefaults([
            'name' => $userIdentity->name,
            'email' => $userIdentity->email,
            'country' => $userIdentity->country,
            'city' => $userIdentity->city,
            'street' => $userIdentity->street,
            'addressNumber' => $userIdentity->addressNumber,
            'zip' => $userIdentity->zip,
            'telNumber' => $userIdentity->telNumber,
        ]);
    }
}