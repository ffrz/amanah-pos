import { ref, computed, onMounted, onUnmounted } from "vue";

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

  const currentDate = computed(() => {
    return currentDateTime.value.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  });

  const currentTime = computed(() => {
    return currentDateTime.value.toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: true,
    });
  });

  return {
    currentDate,
    currentTime,
  };
}
