<?php namespace Controllers\test;

use Inc\utils\UCaptcha;

class T_ReCaptcha extends _controller
{

    public function verificar()
    {
        $token = '03AGdBq27N9FS1X7yuC9mTFTZgtuw-e3A_OP603_fPvGz7JMun8N5H7OBfR35mVmyKO9J1jMK6rR60b-rifTilfDEiHtf_FYnOk0d_dN2uQLD2U-mrl3jl7N7z-YxW1HYn20uKCtppoTA3ZzscZlfxj0FQJwaxBuqA_ubMY8zdhORGfWKenov72L0-d1j1BmIJKINSKJZ1_SKY31JKINfhz6ByCPAYIo0pLXVu-C-iBKay8GxtGa2LwblSo5Z8Os34Vsyp-ktsZX8Cw4bbfSsCJUo7W3Z8bVp_YpZbugIJ-g3Lxvkg_RkoQBTCZ4EoW17zxEmq3HC9U15B8OeJZNj65XiyF6V5U4DU5v3KAoEXpQd2avUIvGKWCXDIxeMG77nKL8QWMqZxcPEBjbIPHwZzbwYo0zu4HRsKqNpnvOtoRkSB174hrMmhF-pleVrkfxU1LaaukrqvUT9o';

        return UCaptcha::verify($token);
    }

}