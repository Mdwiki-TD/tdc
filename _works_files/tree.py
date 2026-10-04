from pathlib import Path

from directory_tree import DisplayTree

main_project_path = Path(__file__).parent.parent

skip_list = [
    "__pycache__",
    "old",
    "post_test",
    "app1.py",
    "load_env.php",
    "example.env",
    "*.html",
    "*.7z",
]

admin_path = main_project_path / "src/app/Coordinator/Admin"

paths = [
    (main_project_path / "src", Path(__file__).parent / "tree.md"),
    (admin_path, admin_path / "README.md"),
    (main_project_path / "src/app", main_project_path / "src/app/README.md"),
    (main_project_path / "tests", Path(__file__).parent / "test_tree.md"),
]

for work_path, save_path in paths:
    tree: str = DisplayTree(
        dirPath=str(work_path),
        stringRep=True,
        header=False,
        maxDepth=float("inf"),
        showHidden=False,
        ignoreList=skip_list,
        onlyFiles=False,
        onlyDirs=False,
        sortBy=2,
        raiseException=False,
        printErrorTraceback=False,
    )

    save_path.write_text(f"```\n{tree}\n```", encoding="utf-8")
