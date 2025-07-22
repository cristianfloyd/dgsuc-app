<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Rule;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Form;

class ContactUsForm extends Form
{
    use WithFileUploads;

    #[Rule(['required', 'string', 'min:5', 'max:255'])]
    public $name;

    #[Rule(['required', 'email', 'max:255'])]
    public $email;

    #[Rule(['nullable', 'string', 'max:255'])]
    public $phone;

    #[Rule(['required', 'string', 'min:5'])]
    public $message;

    #[Rule(['required'])]
    #[Rule(['images.*' => 'max:2024|image'])]
    public $images;

    public function sendEmail(): void
    {
        sleep(3);
    }
}
