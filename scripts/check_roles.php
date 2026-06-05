foreach (\App\Models\Role::withCount('users')->orderBy('id')->get() as $r) {
    echo $r->id . ' | ' . $r->slug . ' | ' . $r->nama . ' | users=' . $r->users_count . PHP_EOL;
}
