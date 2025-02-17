document.addEventListener('livewire:init', () => {
    Livewire.on('print-documentation', () => {
        window.print();
    });
});
