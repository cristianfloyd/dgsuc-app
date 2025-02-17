document.addEventListener('livewire:initialized', () => {
    Livewire.on('print-documentation', () => {
        window.print();
    });
});
