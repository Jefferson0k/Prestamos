<script lang="ts">
export { default as DatePicker } from './DatePicker.vue'
export { default as DatePickerContent } from './DatePickerContent.vue'
</script>

<script setup lang="ts">
import { type DateValue } from '@internationalized/date'
import { useVModel } from '@vueuse/core'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Calendar as CalendarIcon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import DatePickerContent from './DatePickerContent.vue'

const props = defineProps<{
  modelValue?: DateValue | null
  placeholder?: string
  class?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: DateValue | null): void
}>()

const value = useVModel(props, 'modelValue', emit)
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        :variant="'outline'"
        :class="cn(
          'w-full justify-start text-left font-normal',
          !value && 'text-muted-foreground',
          props.class
        )"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        <span v-if="value">
          {{ value.toString() }}
        </span>
        <span v-else>{{ placeholder || 'Pick a date' }}</span>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="p-0">
      <DatePickerContent v-model="value" />
    </PopoverContent>
  </Popover>
</template>

<!-- components/ui/date-picker/DatePickerContent.vue -->
<script setup lang="ts">
import { type DateValue } from '@internationalized/date'
import { useVModel } from '@vueuse/core'
import { Calendar } from '@/components/ui/calendar'

const props = defineProps<{
  modelValue?: DateValue | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: DateValue | null): void
}>()

const value = useVModel(props, 'modelValue', emit)
</script>

<template>
  <Calendar v-model="value" />
</template>

<!-- Example Usage in Loan Form -->
<script setup lang="ts">
import { ref } from 'vue'
import { today, parseDate } from '@internationalized/date'
import { DatePicker } from '@/components/ui/date-picker'

const fechaInicio = ref<DateValue | null>(today())
const fechaFin = ref<DateValue | null>(null)
</script>

<template>
  <div class="grid gap-2">
    <Label>Fecha de Inicio</Label>
    <DatePicker
      v-model="fechaInicio"
      placeholder="Seleccionar fecha de inicio"
    />
  </div>
</template>
