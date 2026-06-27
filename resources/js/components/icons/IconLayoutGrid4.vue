<template>
    <svg v-bind="$attrs" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <rect
            v-for="(cell, index) in cells"
            :key="index"
            :x="cell.x"
            :y="cell.y"
            width="3.75"
            height="3.75"
            rx="0.65"
        />
    </svg>
</template>

<script setup>
const columns = 4;
const rows = 4;
const cellSize = 3.75;
const gap = 0.85;
const gridWidth = columns * cellSize + (columns - 1) * gap;
const gridHeight = rows * cellSize + (rows - 1) * gap;
const originX = (24 - gridWidth) / 2;
const originY = (24 - gridHeight) / 2;

const cells = Array.from({ length: columns * rows }, (_, index) => {
    const column = index % columns;
    const row = Math.floor(index / columns);

    return {
        x: originX + column * (cellSize + gap),
        y: originY + row * (cellSize + gap),
    };
});
</script>
