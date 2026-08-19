<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\HttpFoundation\Response;

class DocsController extends Controller
{
    /**
     * Slug => [markdown file, display title]. An allowlist, not a raw path
     * param — the URL segment never touches the filesystem directly.
     */
    private const PAGES = [
        'setup' => ['SETUP.md', 'Setup Guide'],
        'user-guide' => ['USER_GUIDE.md', 'User Guide'],
        'architecture' => ['ARCHITECTURE.md', 'Architecture'],
    ];

    public function index(): View
    {
        return view('docs.index', ['pages' => self::PAGES]);
    }

    public function show(string $page): View|Response
    {
        if (! isset(self::PAGES[$page])) {
            abort(404);
        }

        [$filename, $title] = self::PAGES[$page];
        $path = base_path("docs/{$filename}");

        if (! is_file($path)) {
            abort(404);
        }

        // GFM (not plain CommonMark) — the docs use pipe tables throughout,
        // which are a GitHub-Flavored Markdown extension, not core CommonMark.
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = $converter->convert(file_get_contents($path))->getContent();

        return view('docs.show', [
            'title' => $title,
            'html' => $html,
            'currentPage' => $page,
            'pages' => self::PAGES,
        ]);
    }
}
