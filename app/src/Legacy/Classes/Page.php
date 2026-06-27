<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use Chevereto\Config\Config;
use Exception;
use function Chevereto\Legacy\assertMaxCount;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\unlinkIfExists;
use function Chevereto\Vars\get;
use function Chevereto\Vars\post;

class Page
{
    public static array $table_fields = [
        'url_key',
        'type',
        'link_url',
        'icon',
        'title',
        'description',
        'keywords',
        'is_active',
        'is_link_visible',
        'attr_target',
        'attr_rel',
        'sort_display',
        'internal',
        'code',
    ];

    public static function getSingle(string $var, string $by = 'url_key', bool $withCode = true): array
    {
        return self::get(
            values: [
                $by => $var,
            ],
            limit: 1,
            withCode: $withCode,
        );
    }

    public static function getAll(array $args = [], array $sort = [], bool $withCode = false): array
    {
        return self::get($args, $sort, null, $withCode);
    }

    public static function get(array $values, array $sort = [], ?int $limit = null, bool $withCode = false): array
    {
        $columns = [
            'page_id',
            'page_url_key',
            'page_type',
            'page_file_path',
            'page_link_url',
            'page_icon',
            'page_title',
            'page_description',
            'page_keywords',
            'page_is_active',
            'page_is_link_visible',
            'page_attr_target',
            'page_attr_rel',
            'page_sort_display',
            'page_internal',
        ];
        if ($withCode) {
            $columns[] = 'page_code';
        }
        $return = DB::get('pages', $values, 'AND', $sort, $limit, columns: $columns);
        if (is_bool($return)) {
            $return = null;
        }
        if (is_array($return[0] ?? false)) {
            foreach ($return as $k => $v) {
                self::formatRowValues($return[$k], $v);
            }
        } elseif (is_array($return) && $return !== []) {
            self::formatRowValues($return);
        }

        return $return ?? [];
    }

    public static function getPath(?string $var = null): string
    {
        return PATH_PUBLIC_CONTENT_PAGES . (is_string($var) ? $var : '');
    }

    public static function getFields(): array
    {
        $fields = self::$table_fields;
        if (Config::enabled()->phpPages()) {
            $fields[] = 'file_path';
        }

        return $fields;
    }

    public static function update(int $id, array $values): int
    {
        $page = self::getSingle((string) $id, 'id', false);
        $return = DB::update('pages', $values, [
            'id' => $id,
        ]);
        if ($return) {
            Cache::instance()->delete('pages_visible');
            Cache::instance()->delete(
                static::getCacheKey($page['url_key'])
            );
        }

        return $return;
    }

    public static function writePage(array $args = []): bool
    {
        if (! $args['file_path']) {
            throw new Exception('Missing file_path argument', 600);
        }
        $file_path = self::getPath($args['file_path']);
        $file_dirname = dirname($file_path);
        $code = empty($args['code']) ? null : $args['code'];
        if (! is_dir($file_dirname)) {
            $base_perms = fileperms(self::getPath());
            $old_umask = umask(0);
            if (mkdir($file_dirname, $base_perms, true)) {
                chmod($file_dirname, $base_perms);
                umask($old_umask);
            } else {
                throw new Exception(_s("Can't create %s destination dir", $file_dirname), 600);
            }
        }
        if (file_exists($file_path) && $code == null && filesize($file_path) == 0) {
            return true;
        }
        $fh = fopen($file_path, 'w');
        $st = ! $fh || fwrite($fh, $code ?? '') === false ? false : true;
        fclose($fh);
        if (! $st) {
            throw new Exception(_s("Can't open %s for writing", $file_path), 601);
        }

        return true;
    }

    public static function fill(array &$page): void
    {
        $page['title_html'] = safe_html($page['title'] ?? '');
        $type_tr = [
            'internal' => _s('Internal'),
            'link' => _s('Link'),
        ];
        $page['type_tr'] = $type_tr[$page['type']];
        switch ($page['type']) {
            case 'internal':
                $page['url'] = get_base_url('page/' . $page['url_key']);
                if (empty($page['file_path'])) {
                    $filepaths = [
                        'default' => 'default/',
                        'user' => null, // base
                    ];
                    $file_basename = $page['url_key'] . '.php';
                    foreach ($filepaths as $v) {
                        if (is_readable(self::getPath($v) . $file_basename)) {
                            $page['file_path'] = $v . $file_basename;
                        }
                    }
                } elseif (Config::enabled()->phpPages()) {
                    if ($page['internal'] === 'contact'
                        && (post() !== [] || (get()['sent'] ?? '0' == '1'))
                    ) {
                        $page['file_path'] = 'default/contact.php';
                    }
                }
                $page['file_path_absolute'] = self::getPath($page['file_path']);
                if (Config::enabled()->phpPages()) {
                    if (! file_exists($page['file_path_absolute'])) {
                        self::writePage([
                            'file_path' => $page['file_path'],
                            'code' => $page['code'] ?? '',
                        ]);
                    }
                }

                break;
            case 'link':
                $page['url'] = is_url($page['link_url']) || str_starts_with($page['link_url'], '/')
                    ? $page['link_url']
                    : '';

                break;
        }
        $page['link_attr'] = 'href="' . $page['url'] . '"';
        if ($page['attr_target'] !== '_self') {
            $page['link_attr'] .= ' target="' . $page['attr_target'] . '"';
        }
        if (! empty($page['attr_rel'])) {
            $page['link_attr'] .= ' rel="' . $page['attr_rel'] . '"';
        }
        if (! empty($page['icon'])) {
            $page['title_html'] = '<span class="btn-icon ' . $page['icon'] . '"></span> ' . $page['title_html'];
        }
    }

    public static function formatRowValues(mixed &$values, mixed $row = []): void
    {
        $values = DB::formatRow($row !== [] ? $row : $values, 'page');
        if (is_array($values)) {
            self::fill($values);
        }
    }

    public static function insert(array $values = []): int
    {
        assertMaxCount('pages');

        return DB::insert('pages', $values);
    }

    public static function delete(array|int $page): int
    {
        if (! is_array($page)) {
            $page = self::getSingle((string) $page, 'id');
        }
        if (Config::enabled()->phpPages()
            && $page['type'] == 'internal'
            && $page['file_path_absolute']
            && is_file($page['file_path_absolute'])
        ) {
            unlinkIfExists($page['file_path_absolute']);
        }

        $return = DB::delete('pages', [
            'id' => $page['id'],
        ]);

        if ($return) {
            Cache::instance()->delete('pages_visible');
            Cache::instance()->delete(
                static::getCacheKey($page['url_key'])
            );
        }

        return $return;
    }

    public static function countAll(): int
    {
        $query = 'SELECT COUNT(*) count FROM '
            . DB::getTable('pages')
            . ';';

        return DB::queryFetchSingle($query)['count'];
    }

    public static function getCacheKey(string $urlKey): string
    {
        return 'page:' . $urlKey;
    }
}
