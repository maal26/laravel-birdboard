<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->accessibleProjects();

        return view('projects.index')->withProjects($projects);
    }

    public function show(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.show')->withProject($project);
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store()
    {
        $project = auth()->user()->projects()->create($this->validateRequest());

        if ($tasks = $this->filteredTasks(request('tasks'))) {
            $project->addTasks($tasks);
        }

        if (request()->wantsJson()) {
            return ['message' => $project->path()];
        }

        return redirect($project->path());
    }

    public function edit(Project $project)
    {
        return view('projects.edit')->withProject($project);
    }

    public function update(Project $project)
    {
        $this->authorize('update', $project);

        $project->update($this->validateRequest());

        return redirect($project->path());
    }

    public function destroy(Project $project)
    {
        $this->authorize('manage', $project);

        $project->delete();

        return redirect('/projects');
    }

    protected function validateRequest()
    {
        return request()->validate([
            'title'       => 'sometimes|required',
            'description' => 'sometimes|required',
            'notes'       => 'nullable'
        ]);
    }

    public function filteredTasks(? array $tasks)
    {
        return collect($tasks)->filter(function ($task) {
            return $task['body'];
        })->toArray();
    }
}
