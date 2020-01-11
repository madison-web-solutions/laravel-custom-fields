<template>
    <div class="loading">
        <svg class="lds-spinner" width="104px"  height="104px"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="background: none;">
            <g transform="rotate(0 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-1.0083s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(30 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.9167s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(60 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.8250s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(90 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.7333s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(120 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.6417s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(150 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.5500s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(180 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.4583s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(210 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.3667s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(240 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.2750s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(270 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.18333s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(300 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="-0.0917s" repeatCount="indefinite"></animate>
                </rect>
            </g>
            <g transform="rotate(330 50 50)">
                <rect x="47.5" y="25" rx="3.325" ry="1.75" width="5" height="12" fill="#040445">
                    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1.1s" begin="0s" repeatCount="indefinite"></animate>
                </rect>
            </g>
        </svg>
        <div><slot>loading</slot></div>
    </div>
</template>

<script>
export default {};
</script>
