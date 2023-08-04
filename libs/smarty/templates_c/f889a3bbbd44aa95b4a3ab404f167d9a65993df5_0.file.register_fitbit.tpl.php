<?php
/* Smarty version 3.1.48, created on 2023-07-12 17:02:49
  from 'C:\xampp\htdocs\primax-dreams-api\views\register_fitbit.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.48',
  'unifunc' => 'content_64af2309989d59_10725811',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f889a3bbbd44aa95b4a3ab404f167d9a65993df5' => 
    array (
      0 => 'C:\\xampp\\htdocs\\primax-dreams-api\\views\\register_fitbit.tpl',
      1 => 1689199362,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_64af2309989d59_10725811 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>

    <a href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
">Entrar a mi app</a>


    <form action="http://10.0.2.2/primax-dreams-api/app/fitbit/create" method="POST">
        <div class="campo">
            <label for="">Ingrese su correo para validar sus datos</label>
            <input type="email" name="email" placeholder="correo@correo">
            <input type="text" name="code" value="<?php echo $_smarty_tpl->tpl_vars['code']->value;?>
">
            <input type="text" name="state" value="<?php echo $_smarty_tpl->tpl_vars['state']->value;?>
">
        </div>
        <input type="submit" value="Enviar">
    </form>

</body>
</html><?php }
}
