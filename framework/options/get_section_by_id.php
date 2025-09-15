<?php
/**
 * get section key and data by section id
 * @param  [type] $sections [description]
 * @param  [type] $id       [description]
 * @return [type]           [description]
 */
function get_section_by_id($sections, $id)
{
    foreach ($sections as $key => $section) {
        if ($section['id'] == $id) {
            return array('key' => $key, 'section' => $section);
        }
    }
}

/**
 * get field ket and data by field id
 * @param  [type] $fields [description]
 * @param  [type] $id     [description]
 * @return [type]         [description]
 */
function get_field_by_id($fields, $id)
{
    foreach ($fields as $key => $field) {
        if (!empty($field['id']) && $field['id'] == $id) {
            return array('key' => $key, 'field' => $field);
        }
    }
}

/**
 * @param array      $array
 * @param int|string $position
 * @param mixed      $insert
 */
function array_insert(&$array, $position, $insert)
{
    if (is_int($position)) {
        return array_splice($array, $position, 0, $insert);
    } else {
        $pos   = array_search($position, array_keys($array));
        $array = array_merge(
            array_slice($array, 0, $pos),
            $insert,
            array_slice($array, $pos)
        );
        return $array;
    }
}