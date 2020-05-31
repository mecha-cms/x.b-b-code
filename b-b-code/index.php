<?php

namespace _\lot\x\b_b_code {
    function title($content) {
        $type = $this->type;
        if ('BBCode' === $type || 'text/bbcode' === $type) {
            return \strtr(\fire(__NAMESPACE__, [$content], $this), [
                '<br>' => ' ',
                '<p>' => "",
                '</p>' => ""
            ]);
        }
        return $content;
    }
    \Hook::set([
        'page.title' // Inline tag(s) only
    ], __NAMESPACE__ . "\\title", 2);
}

namespace _\lot\x {
    function b_b_code($content) {
        $type = $this->type;
        if ('BBCode' === $type || 'text/bbcode' === $type) {
            // Strict URL pattern from <https://gist.github.com/dperini/729294>
            $test = '(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[A-Za-z\d\x{00a1}-\x{ffff}]+-?)*[A-Za-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[A-Za-z\d\x{00a1}-\x{ffff}]+-?)*[A-Za-z\d\x{00a1}-\x{ffff}]+)*(?:\.[A-Za-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?';
            // Encode all HTML special character(s) once, with no mercy!
            $content = \htmlspecialchars(\n($content), \ENT_COMPAT | \ENT_HTML5, 'UTF-8', false);
            // Parse `[code]` element before everything else!
            if (false !== \strpos($content, '[/code]')) {
                $content = \preg_replace_callback('/\[code(=[\w-:.]+)?\]\n*([\s\S]*?)\n*\[\/code\]/', function($m) {
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
                $content = \preg_replace_callback('/\[([bisu])\](.*?)\[\/\1\]/', function($m) {
                    if (false !== \strpos($m[2], '[')) {
                        // Recurse inline element(s)!
                        $m[2] = \strtr(\fire(__NAMESPACE__ . "\\b_b_code", [$m[2]], (object) ['type' => 'BBCode']), [
                            '<br>' => ' ',
                            '<p>' => "",
                            '</p>' => ""
                        ]);
                    }
                    $t = ([
                        'b' => 'strong',
                        'i' => 'em',
                        's' => 'del',
                        'u' => 'ins'
                    ])[$m[1]] ?? "";
                    return $t ? '<' . $t . '>' . $m[2] . '</' . $t . '>' : $m[0];
                }, $content);
                // Parse `[img]` element
                if (false !== \strpos($content, '[/img]')) {
                    $content = \preg_replace_callback('/\[img\](' . $test . ')\[\/img\]/u', function($m) {
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
                    $content = \preg_replace_callback('/\[list(=\d+)?\]\n*([\s\S]*?)\n*\[\/list\]/', function($m) {
                        $i = empty($m[1]) ? "" : \substr($m[1], 1);
                        $i = \is_numeric($i) ? (float) $i : $i;
                        $t = \is_float($i) ? 'ol' . ($i > 1 ? ' start="' . $i . '"' : "") : 'ul';
                        $out = '<' . $t . '>';
                        $out .= \preg_replace([
                            '/^[ ]*\*[ ]*(.*?)$/m', // `* List item`
                            '/^[ ]*\[\*\][ ]*(.*?)(?:[ ]*\[\/\*\])?$/m' // `[*]List item[/*]` or `[*]List item`
                        ], '<li>$1</li>', $m[2]);
                        $out .= '</' . \explode(' ', $t, 2)[0] . '>';
                        return \strtr($out, ["\n" => ""]);
                    }, $content);
                }
                // Parse `[quote]` element
                if (false !== \strpos($content, '[/quote]')) {
                    $content = \preg_replace('/\[quote\]\n*([\s\S]*?)\n*\[\/quote\]/', '<blockquote>$1</blockquote>', $content);
                    // $content = \preg_replace('/\[quote=([^\]]+)\](.*?)\[\/quote\]/', '$0', $content);
                }
                // Parse `[url]` element
                if (false !== \strpos($content, '[/url]')) {
                    $content = \preg_replace('/\[url\](' . $test . ')\[\/url\]/u', '<a href="$1" rel="nofollow">$1</a>', $content);
                    $content = \preg_replace('/\[url=(' . $test . ')\](.*?)\[\/url\]/u', '<a href="$1" rel="nofollow">$2</a>', $content);
                }
            }
            // Convert line-break sequence into paragraph
            $content = '<p>' . \strtr($content, [
                "\n\n" => '</p><p>',
                "\n" => '<br>'
            ]) . '</p>';
            // Clean-up…
            $content = \strtr($content, [
                '<p><blockquote>' => '<blockquote><p>',
                '</blockquote></p>' => '</p></blockquote>'
            ]);
            // Clean-up…
            $content = \strtr($content, [
                '<p><ol>' => '<ol>',
                '</ol></p>' => '</ol>',
                '<p><pre>' => '<pre>',
                '</pre></p>' => '</pre>',
                '<p><ul>' => '<ul>',
                '</ul></p>' => '</ul>'
            ]);
            // Parse smiley pattern outside of HTML tag(s) and code block!
            $parse = function($in) {
                $dir = \To::URL(__DIR__ . \DS . 'lot' . \DS . 'asset' . \DS . 'png');
                $r = [];
                foreach ([
                    'cool' => ['B)', 'B-)'],
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
                        $r[$vv] = '<img alt="' . $vv . '" height="15" src="' . $dir . '/' . $k . '.png" style="display: inline; vertical-align: middle;" width="15">';
                    }
                }
                return \strtr($in, $r);
            };
            $parts = \preg_split('/(<pre(?:\s[^>]*)?><code(?:\s[^>]*)?>[\s\S]*?<\/code><\/pre>|<[^>]+>)/', $content, null, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
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
                    $content .= $parse($part);
                }
            }
        }
        return $content;
    }
    \Hook::set([
        'page.content',
        'page.description'
    ], __NAMESPACE__ . "\\b_b_code", 2);
}
