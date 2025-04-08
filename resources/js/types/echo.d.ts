import Echo from 'laravel-echo';

declare global {
    interface Window {
        Echo: Echo;
        Pusher: any;
    }
}

export interface PusherMessage {
    id: number;
    message: string;
    sender: {
        id: number;
        name: string;
    };
    status: string;
    created_at: string;
    receiver_id: number;
}

export interface Notification {
    id: number;
    type: string;
    data: {
        message: string;
        sender?: {
            id: number;
            name: string;
        };
    };
    read_at: string | null;
    created_at: string;
}