<?php

namespace x\b_b_code {
    function span($content) {
        if (!$content) {
            return $content;
        }
        $type = $this->type;
        if ('BBCode' !== $type && 'text/bbcode' !== $type) {
            return $content;
        }
        return \trim(\strip_tags(\fire(__NAMESPACE__, [$content], $this), [
            'a',
            'br',
            'del',
            'em',
            'img',
            'ins',
            'strong'
        ]));
    }
    \Hook::set([
        'page.description',
        'page.title' // Inline tag(s) only
    ], __NAMESPACE__ . "\\span", 2);
}

namespace x {
    function b_b_code($content) {
        if (!$content) {
            return $content;
        }
        $type = $this->type;
        if ('BBCode' !== $type && 'text/bbcode' !== $type) {
            return $content;
        }
        // Strict URL pattern from <https://gist.github.com/dperini/729294>
        $test = '(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[A-Za-z\d\x{00a1}-\x{ffff}]+-?)*[A-Za-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[A-Za-z\d\x{00a1}-\x{ffff}]+-?)*[A-Za-z\d\x{00a1}-\x{ffff}]+)*(?:\.[A-Za-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?';
        // Encode all HTML special character(s) once, with no mercy!
        $content = \htmlspecialchars(\n($content), \ENT_COMPAT | \ENT_HTML5, 'UTF-8', false);
        // Parse `[code]` element before everything else!
        if (false !== \strpos($content, '[/code]')) {
            $content = \preg_replace_callback('/\[code(=[-.:\w]+)?\]\n*([\s\S]*?)\n*\[\/code\]/', static function ($m) {
                $m[2] = \strtr($m[2], [
                    // Convert line-break into hard-break so they won’t be converted into paragraph
                    "\n" => '<br>',
                    // Escape square bracket(s) so we can preserve BBCode syntax in this code block
                    '[' => '&#x5B;',
                    ']' => '&#x5D;'
                ]);
                return '<pre><code' . (empty($m[1]) ? "" : ' class="' . \substr($m[1], 1) . '"') . '>' . $m[2] . '</code></pre>';
            }, $content);
        }
        if (false !== \strpos($content, '[')) {
            // Parse `[b]`, `[i]`, `[s]` and `[u]` element
            $span_any = '/\[([bisu])\](.*?)\[\/\1\]/';
            $span_any_task = static function ($m) use (&$span_any, &$span_any_task) {
                if (false !== \strpos($m[2], '[')) {
                    // Recurse inline element(s)!
                    $m[2] = \preg_replace_callback($span_any, $span_any_task, $m[2]);
                }
                $any = ([
                    'b' => 'strong',
                    'i' => 'em',
                    's' => 'del',
                    'u' => 'ins'
                ])[$m[1]] ?? "";
                return $any ? '<' . $any . '>' . $m[2] . '</' . $any . '>' : $m[0];
            };
            $content = \preg_replace_callback($span_any, $span_any_task, $content);
            // Parse `[img]` element
            if (false !== \strpos($content, '[/img]')) {
                $content = \preg_replace_callback('/\[img\](' . $test . ')\[\/img\]/u', static function ($m) {
                    // Validate image URL extension
                    $x = \strtolower(\pathinfo($m[1], \PATHINFO_EXTENSION));
                    if (false === \strpos(',apng,gif,jpeg,jpg,png,svg,webp,', ',' . $x . ',')) {
                        return $m[0];
                    }
                    return '<img alt="' . \basename($m[1]) . '" src="' . $m[1] . '">';
                }, $content);
            }
            // Parse `[list]` element
            if (false !== \strpos($content, '[/list]')) {
                $content = \preg_replace_callback('/\[list(=\d+)?\]\n*([\s\S]*?)\n*\[\/list\]/', static function ($m) {
                    $i = empty($m[1]) ? "" : \substr($m[1], 1);
                    $i = \is_numeric($i) ? (float) $i : $i;
                    $any = \is_float($i) ? 'ol' . ($i > 1 ? ' start="' . $i . '"' : "") : 'ul';
                    $out = '<' . $any . '>';
                    $out .= \preg_replace([
                        '/^[ ]*\*[ ]*(.*?)$/m', // `* List item`
                        '/^[ ]*\[\*\][ ]*(.*?)(?:[ ]*\[\/\*\])?$/m' // `[*]List item[/*]` or `[*]List item`
                    ], '<li>$1</li>', $m[2]);
                    $out .= '</' . \explode(' ', $any, 2)[0] . '>';
                    return \strtr($out, ["\n" => ""]);
                }, $content);
            }
            // Parse `[quote]` element
            if (false !== \strpos($content, '[/quote]')) {
                $block_quote = '/\[quote(=[^\s\]]+)?\]\n*((?:(?R)|[\s\S])*?)\n*\[\/quote\]/';
                $block_quote_task = static function ($m) use (&$block_quote, &$block_quote_task) {
                    if (false !== \strpos($m[2], '[/quote]')) {
                        // Recurse!
                        $m[2] = \preg_replace_callback($block_quote, $block_quote_task, $m[2]);
                    }
                    return '<blockquote' . (empty($m[1]) ? "" : 'title="' . \htmlspecialchars($m[1], \ENT_COMPAT | \ENT_HTML5, 'UTF-8', false) . '"') . '>' . $m[2] . '</blockquote>';
                };
                $content = \preg_replace_callback($block_quote, $block_quote_task, $content);
            }
            // Parse `[url]` element
            if (false !== \strpos($content, '[/url]')) {
                $content = \preg_replace('/\[url\](' . $test . ')\[\/url\]/u', '<a href="$1" rel="nofollow">$1</a>', $content);
                $content = \preg_replace('/\[url=(' . $test . ')\](.*?)\[\/url\]/u', '<a href="$1" rel="nofollow">$2</a>', $content);
            }
        }
        // Convert line-break sequence into paragraph
        $content = '<p>' . \strtr($content, [
            '<blockquote>' => '<blockquote><p>',
            '</blockquote>' => '</p></blockquote>',
            "\n\n" => '</p><p>',
            "\n" => '<br>'
        ]) . '</p>';
        // Clean-up…
        $content = \strtr($content, [
            '<p><blockquote>' => '<blockquote>',
            '</blockquote></p>' => '</blockquote>',
            '<br><blockquote>' => '</p><blockquote>',
            '</blockquote><br>' => '</blockquote><p>',
            '<p><ol>' => '<ol>',
            '<p><ol>' => '<ol>',
            '</ol></p>' => '</ol>',
            '<p><pre>' => '<pre>',
            '</pre></p>' => '</pre>',
            '<p><ul>' => '<ul>',
            '</ul></p>' => '</ul>'
        ]);
        // Parse smiley pattern outside of HTML tag(s) and code block!
        $smiley_task = static function ($in) {
            $dir = \To::URL(__DIR__);
            $r = [];
            foreach ([
                'cool' => ['8)', '8-)', 'B)', 'B-)'],
                'grin' => [':D', ':-D'],
                'hmm' => [':/', ':-/', ":\\", ":-\\"],
                'lol' => [],
                'mad' => ['x(', 'X(', 'x-(', 'X-('],
                'rolleyes' => [],
                'sad' => [':(', ':-(', ":'(", ":'-("],
                'smile' => [':)', ':-)'],
                'straight' => [':|', ':-|'],
                'tongue' => [':p', ':P', ':-p', ':-P'],
                'wink' => [';)', ';-)'],
                'yikes' => [':o', ':O', ':-o', ':-O']
            ] as $k => $v) {
                $v = (array) $v;
                $v[] = ':' . $k . ':'; // Add `:name:` syntax
                foreach ($v as $vv) {
                    $r[$vv] = '<img alt="' . $vv . '" height="15" src="' . $dir . '/' . $k . '.png" style="display: inline-block; vertical-align: middle;" width="15">';
                }
            }
            return \strtr($in, $r);
        };
        $parts = \preg_split('/(<pre(?:\s[^>]*)?><code(?:\s[^>]*)?>[\s\S]*?<\/code><\/pre>|<[^>]+>)/', $content, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
        $content = ""; // Reset!
        foreach ($parts as $part) {
            if ($part && '<' === $part[0] && \substr($part, -1) === '>') {
                if ('</pre>' === \substr($part, -6)) {
                    $part = \strtr($part, [
                        // It is now safe to use line-break here
                        '<br>' => "\n",
                        // It is now safe to use plain square bracket character(s) here
                        '&#x5B;' => '[',
                        '&#x5D;' => ']'
                    ]);
                }
                $content .= $part;
            } else {
                $content .= $smiley_task($part);
            }
        }
        return $content;
    }
    \Hook::set([
        'page.content'
    ], __NAMESPACE__ . "\\b_b_code", 2);
}

namespace {
    if (\defined("\\TEST") && 'x.b-b-code' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
        require $test;
    }
}