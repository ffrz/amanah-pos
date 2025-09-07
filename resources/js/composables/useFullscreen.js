// composables/useFullscreen.js
import { ref, watchEffect } from "vue";

export function useFullscreen(targetRef) {
  const isFullscreen = ref(false);

  const requestFullscreen = () => {
    const targetElement = targetRef.value || document.documentElement;
    if (targetElement.requestFullscreen) {
      targetElement.requestFullscreen();
    } else if (targetElement.webkitRequestFullscreen) {
      targetElement.webkitRequestFullscreen();
    } else if (targetElement.mozRequestFullScreen) {
      targetElement.mozRequestFullScreen();
    } else if (targetElement.msRequestFullscreen) {
      targetElement.msRequestFullscreen();
    }
  };

  const exitFullscreen = () => {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    }
  };

  const toggleFullscreen = () => {
    if (isFullscreen.value) {
      exitFullscreen();
    } else {
      requestFullscreen();
    }
  };

  // Sinkronkan status `isFullscreen` dengan API browser
  watchEffect(() => {
    const fullscreenChangeHandler = () => {
      isFullscreen.value = !!document.fullscreenElement;
    };
    document.addEventListener("fullscreenchange", fullscreenChangeHandler);
    document.addEventListener(
      "webkitfullscreenchange",
      fullscreenChangeHandler
    );
    document.addEventListener("mozfullscreenchange", fullscreenChangeHandler);
    document.addEventListener("MSFullscreenChange", fullscreenChangeHandler);

    return () => {
      document.removeEventListener("fullscreenchange", fullscreenChangeHandler);
      document.removeEventListener(
        "webkitfullscreenchange",
        fullscreenChangeHandler
      );
      document.removeEventListener(
        "mozfullscreenchange",
        fullscreenChangeHandler
      );
      document.removeEventListener(
        "MSFullscreenChange",
        fullscreenChangeHandler
      );
    };
  });

  return {
    isFullscreen,
    requestFullscreen,
    exitFullscreen,
    toggleFullscreen,
  };
}
