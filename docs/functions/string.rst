================
String Functions
================

* `str_color`_ - Returns terminal colored string
* `str_style`_ - Returns terminal styled string

-----

.. php:function:: str_color(string $string, string $str_color_name, string $bkg_color_name = 'black')

   Returns terminal colored string

   This is done by escape character so we can actually define a output color. This is done with \033 (\e).

   **Font Colors**

   ======= ============ =========== ==============
    black   dark_grey       red       light_red
    green  light_green     brown        yellow
     blue   light_blue    magenta   light_magenta
     cyan   light_cyan  light_grey      white
   ======= ============ =========== ==============

   **Background Colors**

   ======= ======= ====
    black  yellow  blue
    red    magenta cyan
    green  white       
   ======= ======= ====

   :param string $string: String to be colorized
   :param string $str_color_name: String color name
   :param string $bkg_color_name: Background color name
   :return: ``string`` Color string for terminal output.

   .. rst-class:: wy-text-right

      :ref:`Back to list<String Functions>`

.. php:function:: str_style(string $string, string $style_name)

   Returns terminal styled string

   This is done by escape character so we can actually define a output color. This is done with \033 (\e).

   **Font Styles**

   ======= ========= =============
    bold   italic    hidden
    bright underline strike
    dim    reverse   strikethrough
   ======= ========= =============

   :param string $string: String to be colorized
   :param string $style_name: Style name
   :return: ``string`` Style string for terminal output.

   .. rst-class:: wy-text-right

      :ref:`Back to list<String Functions>`