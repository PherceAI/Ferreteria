import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M30.5 3.5C27.8 3.5 25.4 4.7 23.8 6.6L21.2 4C20.8 3.6 20.1 3.6 19.7 4L15.7 8C15.3 8.4 15.3 9.1 15.7 9.5L18.3 12.1C16.4 13.7 15.2 16.1 15.2 18.8C15.2 24 19.4 28.2 24.6 28.2C27.3 28.2 29.7 27 31.3 25.1L33.9 27.7C34.1 27.9 34.4 28 34.6 28C34.9 28 35.1 27.9 35.3 27.7L39.3 23.7C39.7 23.3 39.7 22.6 39.3 22.2L36.7 19.6C38.6 18 39.8 15.6 39.8 12.9C39.8 7.7 35.6 3.5 30.5 3.5ZM30.5 23.2C22.2 23.2 22.2 14.4 30.5 14.4C38.8 14.4 38.8 23.2 30.5 23.2Z"
                fill="currentColor"
                opacity="0.3"
            />
            <path
                d="M26.5 9.5L22.1 5.1L3.5 23.7L7.9 28.1L26.5 9.5Z"
                fill="currentColor"
            />
            <path
                d="M2 29.5L4.6 36.5C4.9 37.3 5.9 37.6 6.6 37L10.5 33.1L5.9 28.5L2 32.4C1.4 33.1 1.7 34.1 2 29.5Z"
                fill="currentColor"
                opacity="0.7"
            />
            <rect x="1.5" y="28" width="6" height="6" rx="1.5" transform="rotate(-45 1.5 28)" fill="currentColor" opacity="0.8"/>
        </svg>
    );
}
