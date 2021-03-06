<?php

/**
 * sfWidgetFormMultiple is a widget for multiple widgets in form.
 *
 * @package    sfEPFactoryFormPlugin
 * @subpackage widget
 * @author     Vincent CHALAMON <vincentchalamon@gmail.com>
 */
class sfWidgetFormMultiple extends sfWidgetForm {

  /**
   * Configure widget
   * 
   * @param array $options
   * @param array $attributes
   */
  protected function configure($options = array(), $attributes = array()) {
    $this->addRequiredOption("widgets");
    $this->addOption("createLabel", "Créer");
    $this->addOption("max");
    $this->addOption("min");
    $this->addOption("onAdd");
    $this->addOption("onRemove");
  }

  /**
   * Get widget javascripts
   * 
   * @return array
   */
  public function getJavaScripts() {
    $javascripts = array('/sfEPFactoryFormPlugin/js/jquery.min.js', '/sfEPFactoryFormPlugin/jquery-multiple/multiple.jquery.js');
    foreach($this->getOption("widgets") as $widget) {
      if(is_object($widget) && $widget instanceof sfWidgetForm) {
        $javascripts = array_merge($javascripts, $widget->getJavaScripts());
      }
    }
    return $javascripts;
  }

  /**
   * Get widget stylesheets
   * 
   * @return array
   */
  public function getStylesheets() {
    $stylesheets = array('/sfEPFactoryFormPlugin/jquery-multiple/jquery-multiple.css' => 'screen');
    foreach($this->getOption("widgets") as $widget) {
      if(is_object($widget) && $widget instanceof sfWidgetForm) {
        $stylesheets = array_merge($stylesheets, $widget->getStylesheets());
      }
    }
    return $stylesheets;
  }

  /**
   * Render widget
   * 
   * @param string $name Widget name
   * @param mixed $values Widget values (must be null, array or Doctrine_Collection)
   * @param array $attributes Widget attributes
   * @param array $errors Widget errors
   * @return string
   */
  public function render($name, $values = null, $attributes = array(), $errors = array()) {
    // Convert values to array
    if(is_object($values) && $values instanceof Doctrine_Collection) {
      $values = $values->toArray();
    }
    if(!is_array($values)) {
      $values = array();
    }
    $values = array_values($values);
    // Build rows from existing values
    $rows = "";
    foreach($values as $count => $value) {
      if(!$this->getOption("max") || $count <= $this->getOption("max")) {
        $rows.= sprintf('<div class="jquery-multiple-row">%s</div>', $this->renderRow($name, $count, $value));
      }
    }
    if($this->getOption("min")) {
      for($i = $this->getOption("min"); $i > count($values); $i--) {
        $rows.= sprintf('<div class="jquery-multiple-row">%s</div>', $this->renderRow($name, $i-count($values)));
      }
    }
    // Render widget
    return sprintf(<<<EOF
<script type="text/javascript">
  $(document).ready(function(){
    $('#%s_jquery_multiple').multiple({
      max: %s,
      min: %s,
      onAdd: function(event, object){
        %s
      },
      onRemove: function(event, object){
        %s
      }
    });
  });
</script>
<div id="%s_jquery_multiple" class="jquery-multiple">
  <a href="#" class="jquery-multiple-create"%s>%s <span class="jquery-multiple-add">+</span></a>
  <div class="jquery-multiple-source">%s</div>
  $rows
</div>
EOF
            , $this->generateId($name)
            , $this->getOption("max") ? (int)$this->getOption("max") : "null"
            , $this->getOption("min") ? (int)$this->getOption("min") : "null"
            , $this->getOption("onAdd") ? $this->getOption("onAdd") : null
            , $this->getOption("onRemove") ? $this->getOption("onRemove") : null
            , $this->generateId($name)
            , strlen($rows) ? ' style="display: none;"' : null
            , $this->getOption("createLabel")
            , $this->renderRow($name)
            );
  }

  /**
   * Render a row of multiple widgets
   * 
   * @param string $name Main widget name
   * @param mixed $value Row value
   * @return string
   */
  protected function renderRow($name, $position = 0, $value = null) {
    $widgets = "";
    foreach($this->getOption('widgets') as $widgetName => $widget) {
      if(is_object($widget) && $widget instanceof sfWidgetForm) {
        $widgets.= $this->renderWidget($widget, $widgetName, $name."[$position][$widgetName]", isset($value[$widgetName]) ? $value[$widgetName] : null);
      }
      else {
        $widgets.= $widget;
      }
    }
    return sprintf(<<<EOF
<div class="jquery-multiple-elements">
  $widgets
</div>
<div class="jquery-multiple-actions">
  <a href="#" class="jquery-multiple-remove">-</a>
  <a href="#" class="jquery-multiple-add">+</a>
</div>
<div style="clear: both;"></div>
EOF
            );
  }
  
  protected function renderWidget(sfWidgetForm $widget, $widgetName, $name, $value = null) {
    $attributes = $widget->getAttributes();
    return sprintf(<<<EOF
<div class="jquery-multiple-element jquery-multiple-element-$widgetName">
  %s
  %s
</div>
EOF
            , $widget->getLabel() ? sprintf('<label for="%s">%s</label>', $widget->generateId($name, $value), $widget->getLabel()) : null
            , $widget->render($name, $value, $attributes)
            );
  }
}