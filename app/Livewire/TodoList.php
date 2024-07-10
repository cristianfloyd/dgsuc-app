<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name;

    public $search;

    public $editingTodoId = null;
    #[Rule('required|min:3|max:50')]
    public $editingName = null;

    public function create(){
        //validate the input
        $validated = $this->validateOnly('name');

        //create a new todo
        Todo::create($validated);

        //reset the input field
        $this->reset('name');

        //send a message to the user
        session()->flash('success', 'Todo Created Successfully');
        $this->resetPage();
    }

    public function edit($id){
        //find the todo
        $this->editingTodoId = $id;
        $todo = Todo::findOrFail($id);

        //set the name to the input field
        $this->editingName = $todo->name;
    }

    public function update(){

        //find the todo
        $todo = Todo::findOrFail($this->editingTodoId);

        //validate the input
        $validated = $this->validateOnly('editingName');

        //update the todo
        $todo->update([
            'name' => $validated['editingName']
        ]);

        $this->cancelEdit();

        //send a message to the user
        session()->flash('success', 'Todo Updated Successfully');
    }

    public function cancelEdit(){
        //reset the input field
        $this->reset('editingName', 'editingTodoId');
    }

    public function delete($id){
        try{

            //find the todo
            $todo = Todo::findOrFail($id);
            //delete the todo
            $todo->delete();
            //send a message to the user
            session()->flash('success', 'Todo Deleted Successfully');
        } catch (\Exception $e){
            //send a message to the user
            session()->flash('error', 'Todo Deletion Failed');
        }
    }

    public function toggle($id){
        //find the todo
        $todo = Todo::findOrFail($id);

        //toggle the status
        $todo->update([
            'completed' => !$todo->completed
        ]);

        //send a message to the user
        session()->flash('success', 'Todo Updated Successfully');
    }

    public function render()
    {
        $todoList = Todo::latest()
            ->where('name', 'like', '%'.$this->search.'%')
            ->paginate(5);
        return view('livewire.todo-list',[
            'todoList' => $todoList
        ]);
    }
}
