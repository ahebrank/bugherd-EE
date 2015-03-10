<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=bugherd');?>

<?php

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:50%;'),
    lang('setting')
);

foreach ($settings as $key => $val)
{
    $this->table->add_row(lang($key, $key)."<div>".lang('api_description')." <a href='http://www.bugherd.com' target='_blank'>http://www.bugherd.com</a>.</div>", $val);
}

echo $this->table->generate();

?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
<?php $this->table->clear()?>
<?=form_close()?>
<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/bugherd/views/index.php */