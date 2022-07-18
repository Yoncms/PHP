
<?php

return array(
    '{if}'=>'<?php {iff}',

    '{iff}'=>'if(',

    '{:}' => '{)} ?>',

    '{)}' =>'):',

    '{elp}'=>'<?php {elf} ?>',

    '{elf}'=>'elseif:',

    '{else}'=>'<?php {els} ?>',

    '{els}'=>'else:',

    '{/if}'=>'<?php endif ?>',

    '{/foreach}'=>'<?php endforeach ?>',

    '{foreach}' => '<?php {feach}',

    '{feach}'=>'foreach(',

    '{echo}' => '<?php echo ',

    '{endeach}'=>'endforeach;',

    '{endif}'=>'endif;',

    '{/php}' => ' ?>',

    '{php}' => '<?php '
  );

/**
* 函数参数的写法foo{arg_参数}；变量的写法
* 1、直接{变量}，编译是会自动替换成变量值
* 2、{$变量}，编译时不进行替换，3、{var_变量}；
* 字符串的写法{str_字符串},如果是数字不加
* 引号，否则加引号
*/


