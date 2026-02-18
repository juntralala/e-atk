export async function requestNotificationPermission() {
    if(!Notification) {
        return false;
    };
    return await Notification.requestPermission();
}