<?php

namespace App\Livewire;

use App\Models\Contact;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use App\Livewire\Forms\ContactUsForm;

class ContactUs extends Component
{
    use WithFileUploads;
    public ContactUsForm $form;

    public function submitForm()
    {
        $validated = $this->validate();
        // Save to database
        if (is_array($this->form->images)) {
            foreach ($this->form->images as $image) {
                $image->store('images', 'public');
            }
        }
        Contact::create($validated);
        // Send email
        $this->form->sendEmail();

        $this->form->reset();

        session()->flash('success', 'Your message has been sent successfully.');
    }

    public function closeAlert()
    {
        session()->forget('success');
    }

    public function render()
    {
        return view('livewire.contact-us');
    }
}
