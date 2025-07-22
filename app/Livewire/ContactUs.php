<?php

namespace App\Livewire;

use App\Livewire\Forms\ContactUsForm;
use App\Models\Contact;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContactUs extends Component
{
    use WithFileUploads;

    public ContactUsForm $form;

    public function submitForm(): void
    {
        $validated = $this->validate();
        // Save to database
        if (\is_array($this->form->images)) {
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

    public function closeAlert(): void
    {
        session()->forget('success');
    }

    public function render()
    {
        return view('livewire.contact-us');
    }
}
