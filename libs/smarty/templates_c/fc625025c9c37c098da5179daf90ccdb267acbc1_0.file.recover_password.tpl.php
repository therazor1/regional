<?php
/* Smarty version 3.1.44, created on 2022-03-14 18:26:52
  from '/var/www/html/primax/primaxdreams.focusit.pe/api/views/emails/recover_password.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.44',
  'unifunc' => 'content_622fb31cbc2800_27369638',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fc625025c9c37c098da5179daf90ccdb267acbc1' => 
    array (
      0 => '/var/www/html/primax/primaxdreams.focusit.pe/api/views/emails/recover_password.tpl',
      1 => 1647289563,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_622fb31cbc2800_27369638 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_827584141622fb31cbbf0a1_78396017', 'content');
$_smarty_tpl->inheritance->endChild($_smarty_tpl, './layouts/base.tpl');
}
/* {block 'content'} */
class Block_827584141622fb31cbbf0a1_78396017 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'content' => 
  array (
    0 => 'Block_827584141622fb31cbbf0a1_78396017',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <div>
        Hola <span style="font-weight:bold"><?php echo $_smarty_tpl->tpl_vars['name']->value;?>
</span>,
    </div>
    <div style="margin-top:24px">
        Recibimos una solicitud para restablecer tu contraseña <?php echo $_smarty_tpl->tpl_vars['stg']->value->brand;?>
. Haz clic en el enlace para elegir una
        nueva:
    </div>
    <div style="margin-top:24px">
        <a style="background:<?php echo $_smarty_tpl->tpl_vars['stg']->value->color_accent;?>
;padding:0 20px;color:#FFFFFF!important;border-radius:20px;
                text-decoration:none;display:inline-block; height:40px; font-size:14px; font-weight:bold;
                line-height:40px;"
           href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
">
            RESTABLECER TU CONTRASEÑA
        </a>
    </div>
<?php
}
}
/* {/block 'content'} */
}
