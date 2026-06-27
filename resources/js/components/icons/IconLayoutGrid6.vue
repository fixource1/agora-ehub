<template>
    <svg v-bind="$attrs" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <rect
            v-for="(cell, index) in cells"
            :key="index"
            :x="cell.x"
            :y="cell.y"
            width="2.25"
            height="2.75"
            rx="0.45"
        />
    </svg>
</template>

<script setup>
const columns = 6;
const rows = 3;
const cellWidth = 2.25;
const cellHeight = 2.75;
const gapX = 0.45;
const gapY = 0.55;
const gridWidth = columns * cellWidth + (columns - 1) * gapX;
const gridHeight = rows * cellHeight + (rows - 1) * gapY;
const originX = (24 - gridWidth) / 2;
const originY = (24 - gridHeight) / 2;

const cells = Array.from({ length: columns * rows }, (_, index) => {
    const column = index % columns;
    const row = Math.floor(index / columns);

    return {
        x: originX + column * (cellWidth + gapX),
        y: originY + row * (cellHeight + gapY),
    };
});
</script>
