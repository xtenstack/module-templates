<?php

use App_skeleton\Models\SoftDeletes;

/**
 * Demo entity for the plugin-template module — deliberately trivial (name
 * + description, nothing else meaningful). Copy this shape (bare global
 * class, SoftDeletes trait, keepSnapshots) for a real module's own models.
 */
class Tag extends \Phalcon\Mvc\Model
{
    use SoftDeletes;

    public $id;
    public $name;
    public $description;
    public $deleted_at;
    public $created_at;
    public $updated_at;

    public function initialize()
    {
        $this->setSource('tags');
        $this->keepSnapshots(true);
    }

    public function beforeSave()
    {
        $this->updated_at = date('Y-m-d H:i:s');
    }
}
