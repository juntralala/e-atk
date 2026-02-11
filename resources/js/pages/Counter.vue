<script setup>
import { defineComponent, nextTick, onMounted, ref } from 'vue';

const counter = ref(0);

const handleClick = (async function() {
  counter.value++;
  counter.value++;
  await new Promise(function(resolve) {
    setTimeout(function() {
      resolve(true);
    }, 3000);
  });
  counter.value++;
});


const MyComp = {
  setup() {
    return {
      name: "ujun"
    }
  },
  render(a) {
    return a.name; 
  }
};

</script>

<template>
  <button @click="handleClick">Tombol</button>
  <h1>{{counter}}</h1>
  <MyComp/>
</template>