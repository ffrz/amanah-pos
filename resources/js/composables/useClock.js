import { ref, onMounted, onUnmounted } from "vue";

export function useClock() {
  const currentDateTime = ref(new Date());
  let timeInterval = null;

  const updateTime = () => {
    currentDateTime.value = new Date();
  };

  onMounted(() => {
    timeInterval = setInterval(updateTime, 1000);
  });

  onUnmounted(() => {
    if (timeInterval) {
      clearInterval(timeInterval);
    }
  });

  return {
    currentDateTime,
  };
}
