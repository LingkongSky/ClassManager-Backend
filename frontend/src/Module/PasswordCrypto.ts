import sha1 from 'sha1'
export function PasswordHash(password: string) {
    return sha1(password);
}

