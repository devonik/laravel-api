
import { PageProps } from '@/types';

export default function Welcome({ auth, laravelVersion, phpVersion }: PageProps<{ laravelVersion: string, phpVersion: string }>) {

    return (
        <h1>Welcome to devnik's laravel api</h1>
    );
}
