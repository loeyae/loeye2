<{extends file='layout.tpl'}>
<{block name="title"}>测试<{/block}>
<{block name="body"}>
            <div>
            <{$context_data['test.output']|var_dump}>
            </div>
            <div>
            <{$context_data['test.output1']|var_dump}>
            </div>
<{/block}>
