<template>
    <header class="home-header safe-top">
        <div class="home-header-inner mx-auto max-w-5xl px-4 md:px-8">
            <div class="home-header-lockup flex items-center">
                <div class="home-header-institution shrink-0">
                    <img
                        :src="UP_SEAL_SRC"
                        :alt="UP_SEAL_ALT"
                        class="home-header-seal h-11 w-11 object-contain sm:h-12 sm:w-12 md:h-14 md:w-14"
                    >
                </div>

                <div class="home-header-divider shrink-0" aria-hidden="true" />

                <div class="home-header-app flex min-w-0 flex-1 items-center gap-3 sm:gap-3.5">
                    <BrandLogo
                        size="lg"
                        class="home-header-agora shrink-0 sm:h-[3.25rem] sm:w-[3.25rem] md:h-14 md:w-14"
                    />
                    <div class="min-w-0">
                        <p class="home-header-eyebrow text-muted truncate text-[10px] font-semibold uppercase tracking-[0.14em] sm:text-[11px]">
                            {{ APP_TAGLINE_SHORT }}
                        </p>
                        <h1 class="font-display text-app truncate text-[1.35rem] font-bold leading-tight tracking-tight sm:text-2xl md:text-[1.65rem]">
                            {{ APP_NAME }}
                        </h1>
                        <p class="text-muted mt-0.5 hidden truncate text-xs sm:block">
                            Research &amp; extension resources
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { APP_NAME, APP_TAGLINE_SHORT, UP_SEAL_ALT, UP_SEAL_SRC } from '@/constants/brand';
</script>
