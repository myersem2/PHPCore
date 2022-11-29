.. _rst-sample:

=============
RST Sample
=============

`Sphinx RST Guide <https://sublime-and-sphinx-guide.readthedocs.io/en/latest/index.html>`_

`RST Cheet Sheet <https://docutils.sourceforge.io/docs/user/rst/quickref.html>`_

Code Block
##########

.. code-block:: php
   :caption: ./file.php
   :linenos:
   :lineno-start: 20
   :emphasize-lines: 1,4-6

    class Session
    {
        public function __construct()
        {
            $this->test = 123;
        }
    }

PHP Domain (Class)

.. php:class:: DateTime

  Datetime class

  .. php:method:: setDate($year, $month, $day)

      Set the date.

      :param int $year: The year.
      :param int $month: The month.
      :param int $day: The day.
      :returns: Either false on failure, or the datetime object for method chaining.


  .. php:method:: setTime($hour, $minute[, $second])

      Set the time.

      :param int $hour: The hour
      :param int $minute: The minute
      :param int $second: The second
      :returns: Either false on failure, or the datetime object for method chaining.

  .. php:const:: ATOM

      Y-m-d\TH:i:sP

Glossary
########

.. glossary::

   environment
      A structure where information about all documents under the root is
      saved, and used for cross-referencing.  The environment is pickled
      after the parsing stage, so that successive runs only need to read
      and parse new and changed documents.

   source directory
      The directory which, including its subdirectories, contains all
      source files for one Sphinx project.

Seealso
#######

.. seealso::

   `GNU tar manual, Basic Tar Format <http://link>`_
      Documentation for tar archive files, including GNU tar extensions.

+------------+------------+-----------+
| Header 1   | Header 2   | Header 3  |
+============+============+===========+
| body row 1 | column 2   | column 3  |
+------------+------------+-----------+
| body row 2 | Cells may span columns.|
+------------+------------+-----------+
| body row 3 | Cells may  | - Cells   |
+------------+ span rows. | - contain |
| body row 4 |            | - blocks. |
+------------+------------+-----------+

Simple table:

=====  =====  ======
   Inputs     Output
------------  ------
  A      B    A or B
=====  =====  ======
False  False  False
True   False  True
False  True   True
True   True   True
=====  =====  ======
