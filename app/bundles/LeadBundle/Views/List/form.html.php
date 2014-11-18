<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'leadlist');

$id = $form->vars['data']->getId();
if (!empty($id)) {
    $name   = $form->vars['data']->getName();
    $header = $view['translator']->trans('mautic.lead.list.header.edit', array("%name%" => $name));
} else {
    $header = $view['translator']->trans('mautic.lead.list.header.new');
}
$view['slots']->set("headerTitle", $header);

$glueOptions = array(
    'and' => 'mautic.lead.list.form.glue.and',
    'or'  => 'mautic.lead.list.form.glue.or'
);

//Generate lists for select boxes
$countries = \Symfony\Component\Intl\Intl::getRegionBundle()->getCountryNames();
$tz        = new \Symfony\Component\Form\Extension\Core\Type\TimezoneType();
$timezones = $tz->getTimezones();

echo $view['form']->start($form);
?>

<!-- start: box layout -->
<div class="box-layout">
    <!-- container -->
    <div class="col-md-9 bg-white height-auto">
        <div class="bg-auto bg-dark-xs">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['name']); ?>
            </div>
            <div class="col-md-6">
                <?php echo $view['form']->row($form['alias']); ?>
            </div>
            <div class="pa-md">
                <?php echo $view['form']->row($form['description']); ?>
            </div>
            <ul class="nav nav-tabs pr-md pl-md" role="tablist">
                <li class="active">
                    <a href="#filters" class="group" data-toggle="tab">
                        <?php echo $view['form']->label($form['filters']); ?>
                    </a>
                </li>
            </ul>
        </div>
                <!-- start: tab-content -->
        <div class="tab-content pa-md">
            <div class="tab-pane fade in active bdr-w-0" id="filters">
                <?php
                $filterForm   = $form['filters'];
                $filterValues = $filterForm->vars['data'] ?: array();
                $form['filters']->setRendered();
                ?>
                    <div class="form-group">
                        <?php echo $view['form']->errors($filterForm); ?>
                        <div class="available-filters mb-md">
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo $view['translator']->trans('mautic.lead.list.form.filters.add'); ?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu scrollable-menu" role="menu">
                                    <?php foreach ($choices as $value => $params): ?>
                                        <?php $list = (!empty($params['properties']['list'])) ? $params['properties']['list'] : ''; ?>
                                        <?php $callback = (!empty($params['properties']['callback'])) ? $params['properties']['callback'] : ''; ?>
                                        <li>
                                            <a id="available_<?php echo $value; ?>" class="list-group-item" href="javascript:void(0);" onclick="Mautic.addLeadListFilter('<?php echo $value; ?>');" data-field-type="<?php echo $params['properties']['type']; ?>" data-field-list="<?php echo $list; ?>" data-field-callback="<?php echo $callback; ?>">
                                                <span class="leadlist-filter-name"><?php echo $view['translator']->trans($params['label']); ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="selected-filters">
                            <div class="list-group" id="<?php echo $filterForm->vars['id']; ?>_right">
                                    <?php $i = 0; ?>
                                    <?php foreach ($filterValues as $filter): ?>
                                    <?php if (!isset($choices[$filter['field']])) continue; ?>
                                    <?php $randomId = "id_" . hash('sha1', uniqid(mt_rand())); ?>
                                        <div class="panel">
                                        <?php if ($i != 0): ?>
                                            <div class="panel-footer">
                                                <div class="col-sm-2 pl-0">
                                                    <select name="leadlist[filters][glue][]" class="form-control ">
                                                        <?php
                                                        foreach ($glueOptions as $v => $l):
                                                        $selected = ($v == $filter['glue']) ? ' selected' : '';
                                                        ?>
                                                            <option value="<?php echo $v; ?>"<?php echo $selected; ?>><?php echo $view['translator']->trans($l); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <input name="leadlist[filters][glue][]" type="hidden" value="and" />
                                        <?php endif; ?>
                                        <div class="panel-body">
                                            <div class="col-xs-6 col-sm-3 field-name">
                                                <?php echo $choices[$filter['field']]['label']; ?>
                                            </div>
                                            <div class="col-xs-6 col-sm-3 padding-none">
                                                <select name="leadlist[filters][operator][]" class="form-control ">
                                                    <?php foreach ($operatorOptions as $v => $l): ?>
                                                        <option value="<?php echo $v; ?>"><?php echo $view['translator']->trans($l['label']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-xs-10 col-sm-5 padding-none">
                                                <?php switch ($choices[$filter['field']]['properties']['type']):
                                                    case 'lookup':
                                                    case 'select':
                                                    ?>
                                                    <input type="text" class="form-control"
                                                           name="leadlist[filters][filter][]"
                                                           data-toggle="field-lookup"
                                                           data-target="<?php echo $filter['field']; ?>"
                                                           <?php if (isset($choices[$filter['field']]['properties']['list'])):?>
                                                           data-options="<?php echo $choices[$filter['field']]['properties']['list']; ?>"
                                                           <?php endif; ?>
                                                           placeholder="<?php echo $view['translator']->trans('mautic.lead.list.form.filtervalue'); ?>"
                                                           value="<?php echo $filter['filter']; ?>"
                                                           id="<?php echo $randomId; ?>" />
                                                    <input type="hidden" name="leadlist[filters][display][]" />
                                                    <?php
                                                    break;
                                                    case 'timezone':
                                                    ?>
                                                    <select class="form-control" name="leadlist[filters][filter][]">
                                                    <?php foreach ($timezones as $continent => $zones): ?>
                                                        <optgroup label="<?php echo $continent; ?>" />
                                                        <?php foreach ($zones as $t): ?>
                                                        <?php $selected = ($filter['filter'] == $t) ? ' selected="selected"' : ''; ?>
                                                        <option value="<?php echo $t; ?>"<?php echo $selected; ?>><?php echo $t; ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endforeach; ?>
                                                    </select>
                                                    <input type="hidden" name="leadlist[filters][display][]" />
                                                    <?php
                                                    break;
                                                    case 'country':
                                                    ?>
                                                    <select class="form-control" name="leadlist[filters][filter][]">
                                                    <?php foreach ($countries as $c): ?>
                                                        <?php $selected = ($filter['filter'] == $c) ? ' selected="selected"' : ''; ?>
                                                        <option value="<?php echo $c; ?>"<?php echo $selected; ?>><?php echo $c; ?></option>
                                                    <?php endforeach; ?>
                                                    </select>
                                                    <input type="hidden" name="leadlist[filters][display][]" />
                                                    <?php
                                                    break;
                                                    case 'time':
                                                    case 'date':
                                                    case 'datetime':
                                                    ?>
                                                    <input type="<?php echo $choices[$filter['field']]['properties']['type']; ?>"
                                                           class="form-control"
                                                           name="leadlist[filters][filter][]"
                                                           data-toggle="<?php echo $choices[$filter['field']]['properties']['type']; ?>"
                                                           value="<?php echo $filter['filter'] ?>"
                                                           id="<?php echo $randomId; ?>" />
                                                    <?php
                                                    break;
                                                    case 'lookup_id':
                                                    case 'boolean':
                                                    ?>
                                                    <input type="text" class="form-control"
                                                           name="leadlist[filters][display][]"
                                                           data-toggle="field-lookup"
                                                           data-target="<?php echo $filter['field']; ?>"
                                                            <?php if (isset($choices[$filter['field']]['properties']['list'])):?>
                                                            data-options="<?php echo $choices[$filter['field']]['properties']['list']; ?>"
                                                            <?php endif; ?>
                                                           placeholder="<?php echo $view['translator']->trans('mautic.lead.list.form.filtervalue'); ?>"
                                                           value="<?php echo $filter['display']; ?>"
                                                           id="<?php echo $randomId; ?>" />
                                                    <input type="hidden"
                                                           name="leadlist[filters][filter][]"
                                                           value="<?php echo $filter['filter']; ?>"
                                                           id="<?php echo $randomId."_id"; ?>" />
                                                    <?php break;
                                                    default: ?>
                                                    <input type="<?php echo $choices[$filter['field']]['properties']['type']; ?>"
                                                           class="form-control"
                                                           name="leadlist[filters][filter][]"
                                                           data-toggle="field-lookup"
                                                           data-target="<?php echo $filter['field']; ?>"
                                                           placeholder="<?php echo $view['translator']->trans('mautic.lead.list.form.filtervalue'); ?>"
                                                           value="<?php echo $filter['filter']; ?>"
                                                           id="<?php echo $randomId; ?>" />
                                                    <input type="hidden" name="leadlist[filters][display][]" />
                                                    <?php break;
                                                endswitch; ?>
                                            </div>
                                            <div class="col-xs-2 col-sm-1">
                                                <a href="javascript: void(0);" class="remove-selected btn btn-default text-danger pull-right"><i class="fa fa-trash-o"></i></a>
                                            </div>
                                            <input type="hidden" name="leadlist[filters][field][]" value="<?php echo $filter['field']; ?>" />
                                            <input type="hidden" name="leadlist[filters][type][]" value="<?php echo $filter['type']; ?>" />
                                        </div>
                                        <?php $i++; ?>
                                    </div>
                                    <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 bg-white bdr-l height-auto">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php echo $view['form']->row($form['isGlobal']); ?>
            <?php echo $view['form']->row($form['isPublished']); ?>
        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>

<div id="filter-template" class="hide">
    <div class="panel-footer">
        <div class="col-sm-2 pl-0">
            <select name="leadlist[filters][glue][]" class="form-control">
                <?php foreach ($glueOptions as $v => $l): ?>
                    <option value="<?php echo $v; ?>"><?php echo $view['translator']->trans($l); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="panel-body">
        <div class="col-xs-6 col-sm-3 field-name">

        </div>
        <div class="col-xs-6 col-sm-3 padding-none">
            <select name="leadlist[filters][operator][]" class="form-control ">
                <?php foreach ($operatorOptions as $v => $l): ?>
                    <option value="<?php echo $v; ?>"><?php echo $view['translator']->trans($l['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-xs-10 col-sm-5 padding-none">
            <input type="text" class="form-control" name="leadlist[filters][filter][]"
                   placeholder="<?php echo $view['translator']->trans('mautic.lead.list.form.filtervalue'); ?>" />
            <input type="hidden" name="leadlist[filters][display][]" />
        </div>
        <div class="col-xs-2 col-sm-1">
            <a href="#" class="remove-selected btn btn-default text-danger pull-right"><i class="fa fa-trash-o"></i></a>
        </div>
        <input type="hidden" name="leadlist[filters][field][]" />
        <input type="hidden" name="leadlist[filters][type][]" />
    </div>
</div>

<div id="filter-country-template" class="hide">
    <div class="col-xs-6 col-sm-2 padding-none">
        <select name="leadlist[filters][glue][]" class="form-control ">
            <?php foreach ($glueOptions as $v => $l): ?>
                <option value="<?php echo $v; ?>"><?php echo $view['translator']->trans($l); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-xs-6 col-sm-3 padding-none">
        <select name="leadlist[filters][operator][]" class="form-control ">
            <?php foreach ($operatorOptions as $v => $l): ?>
                <option value="<?php echo $v; ?>"><?php echo $view['translator']->trans($l['label']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-7 padding-none">
        <select class="form-control" name="leadlist[filters][filter][]">
        <?php foreach ($countries as $c): ?>
            <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
        <?php endforeach; ?>
        </select>
        <input type="hidden" name="leadlist[filters][display][]" />
    </div>
    <input type="hidden" name="leadlist[filters][field][]" />
    <input type="hidden" name="leadlist[filters][type][]" />
    <div class="clearfix"></div>
</div>

<div id="filter-timezone-template" class="hide">
    <div class="col-xs-6 col-sm-2 padding-none">
        <select name="leadlist[filters][glue][]" class="form-control ">
            <?php foreach ($glueOptions as $v => $l): ?>
                <option value="<?php echo $v; ?>"><?php echo $view['translator']->trans($l); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-xs-6 col-sm-3 padding-none">
        <select name="leadlist[filters][operator][]" class="form-control ">
            <?php foreach ($operatorOptions as $v => $l): ?>
                <option value="<?php echo $v; ?>"><?php echo $view['translator']->trans($l['label']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-7 padding-none">
        <select class="form-control" name="leadlist[filters][filter][]">
            <?php foreach ($timezones as $continent => $zones): ?>
                <optgroup label="<?php echo $continent; ?>" />
                <?php foreach ($zones as $t): ?>
                <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="leadlist[filters][display][]" />
    </div>
    <input type="hidden" name="leadlist[filters][field][]" />
    <input type="hidden" name="leadlist[filters][type][]" />
    <div class="clearfix"></div>
</div>
