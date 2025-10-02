<!--

\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s*=\s*(['"])\s*<[\s\S]*?>\s*\1;

(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s*=\s*)(['"])(<\s*[\s\S]*?>\s*)\2;
$1<<<HTML\n$3\nHTML;

(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s*=\s*)(['"])(\s*<[\s\S]*?>\s*)\2;

--------
(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*|print|echo)(\s*\.*=\s*)(['"])(\s*)(<[\s\S]*?>\s*)\3;

$1$2<<<HTML$4$5$4HTML;
-->


<div class="card border-danger">
    <div class="card-header">
        <h3 class="headline">404</h3>
    </div>
    <div class="card-body">
        <h4><i class="fas fa-exclamation-triangle text-warning"></i> Page not found.</h4>
    </div>
</div>
