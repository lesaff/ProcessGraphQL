<?php

namespace ProcessWire\GraphQL\Type\Object;

use Youshido\GraphQL\Type\Object\AbstractObjectType;
use ProcessWire\Template;
use ProcessWire\Field;
use ProcessWire\GraphQL\Utils;
use ProcessWire\GraphQL\Type\InterfaceType\PageInterfaceType;

class TemplatedPageType extends AbstractObjectType {

  protected $template;

  public function __construct(Template $template)
  {
    $this->template = $template;
    parent::__construct([]);
  }

  public Static function normalizeName($name)
  {
    return str_replace('-', '_', $name);
  }

  public function getName()
  {
    return ucfirst(self::normalizeName($this->template->name)) . 'PageType';
  }

  public function getDescription()
  {
    $desc = $this->template->description;
    if ($desc) return $desc;
    return "PageType with template `" . $this->template->name . "`.";
  }

  public function build($config)
  {
    $legalFields = Utils::moduleConfig()->legalFields;
    $config->applyInterface(new PageInterfaceType());
    foreach ($this->template->fields as $field) {
      // skip illigal fields
      if (!$legalFields->has($field)) continue;
      // check if user has permission to view this field
      if (!Utils::hasFieldPermission('view', $field, $this->template)) continue;
      // skip if the field type is not supported
      $className = "\\ProcessWire\\GraphQL\\Field\\Page\\Fieldtype\\" . $field->type->className();
      if (!class_exists($className)) continue;
      $config->addField(new $className($field));
    }
  }

  public function getInterfaces()
  {
      return [new PageInterfaceType()];
  }

}
