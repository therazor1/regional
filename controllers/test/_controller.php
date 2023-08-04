<?php namespace Controllers\test;

use ReflectionClass;
use ReflectionMethod;

class _controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $class = new ReflectionClass($this);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $uri = $_SERVER['REQUEST_URI'];

        echo '
            <style>
                *{font-family:Arial,serif;font-size:14px}
                table {border-collapse: collapse;}
                table, th, td {border: 1px solid #EEE;}
                th, td {padding:4px;text-align: left;}
            </style>
            <script>
                function onSubmit(e,method) {
                  let inputs = e.getElementsByTagName("input");
                  let url = "' . $uri . '/" + method;
                  for(let input of inputs){
                      if(input.type === "checkbox"){
                          url += "/" + (input.checked ? "1" : "0");
                      } else {
                          url += "/" + input.value;
                      }
                  }
                  location.href = url;
                  return false;
                }
            </script>
            <a href="/api/test">&laquo; return</a>
            <table style="width:100%;margin-top:8px">
              <tr>
                <th style="width:1%">method</th>
                <th>parameters</th>
              </tr>
        ';

        foreach ($methods as $method) {
            if (!in_array($method->name, ['index', '__construct'])) {

                echo '<tr>';

                echo '
                    <td>
                        <a href="' . $uri . '/' . $method->name . '">' . $method->name . '</a>
                    </td>
                ';

                echo '<td>';

                $params = $method->getParameters();

                if ($params) {
                    echo '<form onsubmit="return onSubmit(this,\'' . $method->name . '\');" style="margin:0">';
                    echo '<button>&raquo;</button>';
                    foreach ($params as $param) {
                        $type = $param->getType();
                        $value = $param->getDefaultValue();
                        $lenvalue = strlen($value);
                        if ($type == 'bool') {
                            echo '
                                <label>
                                    <input name="' . $param->name . '"
                                           type="checkbox"
                                           ' . ($value ? 'checked' : '') . '> ' . $param->name . '
                                </label>
                           ';
                        } else {
                            echo '
                                <input name="' . $param->name . '"
                                       placeholder="' . $param->name . '"
                                       size="' . ($lenvalue > 80 ? 80 : ($lenvalue < 3 ? 3 : $lenvalue)) . '"
                                       title="' . $param->name . '"
                                       value="' . $value . '">
                           ';
                        }
                    }
                    echo '</form>';
                }

                echo '</td>';

                echo ' </tr > ';
            }
        }

        echo '</table > ';
    }

}