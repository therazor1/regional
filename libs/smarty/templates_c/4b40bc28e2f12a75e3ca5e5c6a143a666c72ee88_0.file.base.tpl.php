<?php
/* Smarty version 3.1.44, created on 2022-03-14 18:26:52
  from '/var/www/html/primax/primaxdreams.focusit.pe/api/views/emails/layouts/base.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.44',
  'unifunc' => 'content_622fb31cbcdc70_75112022',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4b40bc28e2f12a75e3ca5e5c6a143a666c72ee88' => 
    array (
      0 => '/var/www/html/primax/primaxdreams.focusit.pe/api/views/emails/layouts/base.tpl',
      1 => 1647289563,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_622fb31cbcdc70_75112022 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
?>
<div style="background:#f2f2f2;font-family:Helvetica,Arial,sans-serif;font-size:12px;">

    <div style="margin:0 auto; max-width:600px;">

        <div style="padding:4px 4px;text-align:center;background-color:<?php echo $_smarty_tpl->tpl_vars['stg']->value->color_primary;?>
">
            <img style="max-height:50px" src="<?php echo pic($_smarty_tpl->tpl_vars['stg']->value->pic_logo);?>
" alt="logo"/>
        </div>

        <div style="background:white; padding:40px;font-size:14px;line-height:20px">
            <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_1947709412622fb31cbcc1a3_15214181', 'content');
?>

        </div>

        <div style="padding:24px;background-color:<?php echo $_smarty_tpl->tpl_vars['stg']->value->color_primary;?>
;color:rgba(255,255,255,.8)">
            <div style="font-weight:bold;line-height:18px; font-size:14px">
                No responder
            </div>
            <div style="margin-top:8px;line-height:18px; font-size:14px">
                Este es un correo automático, por favor no responder
            </div>
            <div style="text-align:center;font-size:12px;border-top:1px solid rgba(255,255,255,.1);
                    margin-top:24px;padding-top:24px">
                El equipo de <?php echo $_smarty_tpl->tpl_vars['stg']->value->brand;?>

            </div>
        </div>

    </div>

</div><?php }
/* {block 'content'} */
class Block_1947709412622fb31cbcc1a3_15214181 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'content' => 
  array (
    0 => 'Block_1947709412622fb31cbcc1a3_15214181',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'content'} */
}
