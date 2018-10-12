<template>
    <div class="lcf-field" :data-lcf-field-type="type">
        <component :is="field.inputComponent" :path="path" :field="field" :initialValue="initialValue" :errors="errors" />
        <p v-if="myErrors.length" class="lcf-field-errors"><span v-for="error in myErrors">{{ error }}</span></p>
        <p v-if="help" class="lcf-help">{{ help }}</p>
    </div>
</template>

<script>
import _ from 'lodash';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    computed: {
        type: function() {
            return _.get(this.field, 'type', null);
        },
        myErrors: function() {
            return this.errors[this.path.join('.')] || [];
        },
        help: function() {
            return _.get(this.field, 'settings.help', null);
        }
    }
};
</script>
