<script setup>
import { ref } from 'vue';

const counter = ref(0);

const handleClick = async function () {
  counter.value++;
  counter.value++;
  await new Promise(function (resolve) {
    setTimeout(function () {
      resolve(true);
    }, 3000);
  });
  counter.value++;
};

const MyComp = {
  setup() {
    return {
      name: 'ujun',
    };
  },
  render(a) {
    return a.name;
  },
};

defineOptions({
  mounted: function () {
    alert(window.isSecureContext);
  },
});
</script>

<template>
  <button @click="handleClick">Tombol</button>
  <MyComp />
</template>
