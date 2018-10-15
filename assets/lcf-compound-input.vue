<template>
    <div class="lcf-compound-field">
        <div class="lcf-compound-item" v-for="child, key in children" :key="key" >
            <div class="lcf-compound-key">
                <label>{{ key }}</label>
            </div>
            <lcf-field :path="path.concat(key)" :field="child.field" :initialValue="child.initialValue" :errors="errors" />
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
var counter = 0;
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    data: function() {
        var children = {};
        for (var key in this.field.options.sub_fields) {
            var subField = this.field.options.sub_fields[key];
            children[key] = {
                field: subField,
                initialValue: _.get(this.initialValue, key, null)
            };
        }
        return {
            children: children
        };
    }
};
</script>
