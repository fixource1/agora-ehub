import { isNativePlatform } from '@/config/api';

export function shouldUseMobilePdfViewer() {
    return isNativePlatform();
}
