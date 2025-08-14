<template>
    <div class="scanner-container">
        <!-- Live Camera Scanner -->
        <div class="flex items-center justify-center">
            <div
                v-show="!isLoading"
                class="relative w-full max-w-sm aspect-video"
            >
                <video
                    poster="data:image/gif,AAAA"
                    ref="scanner"
                    class="w-full h-full object-contain rounded-lg shadow"
                ></video>
                <div class="overlay-element"></div>
                <div class="laser"></div>
            </div>
        </div>
    </div>
</template>

<script>
import { BrowserMultiFormatReader, Exception } from "@zxing/library";

export default {
    name: "BarcodeScanner",

    data() {
        return {
            isLoading: true,
            codeReader: new BrowserMultiFormatReader(),
            isMediaStreamAPISupported:
                navigator &&
                navigator.mediaDevices &&
                "enumerateDevices" in navigator.mediaDevices,
        };
    },

    mounted() {
        if (!this.isMediaStreamAPISupported) {
            throw new Exception("Media Stream API is not supported");
        }

        this.startLiveScanner();

        this.$refs.scanner.oncanplay = () => {
            this.isLoading = false;
            this.$emit("loaded");
        };
    },

    beforeUnmount() {
        this.codeReader.reset();
    },

    methods: {
        // Live scanner using camera
        startLiveScanner() {
            this.codeReader.decodeFromVideoDevice(
                undefined,
                this.$refs.scanner,
                (result, err) => {
                    if (result) {
                        this.$emit("decode", result.text);
                        this.$emit("result", result);
                    }
                }
            );
        },
    },
};
</script>

<style scoped>
video {
    max-width: 100%;
    max-height: 100%;
}

.scanner-container {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.overlay-element {
    position: absolute;
    top: 0;
    width: 100%;
    height: 99%;
    background: rgba(30, 30, 30, 0.5);
    clip-path: polygon(
        0% 0%,
        0% 100%,
        20% 100%,
        20% 20%,
        80% 20%,
        80% 80%,
        20% 80%,
        20% 100%,
        100% 100%,
        100% 0%
    );
}

.laser {
    width: 60%;
    margin-left: 20%;
    background-color: tomato;
    height: 1px;
    position: absolute;
    top: 40%;
    z-index: 2;
    box-shadow: 0 0 4px red;
    animation: scanning 2s infinite;
}

@keyframes scanning {
    50% {
        transform: translateY(75px);
    }
}
</style>
