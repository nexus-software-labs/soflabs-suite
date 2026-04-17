import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

/**
 * Aviso cuando el usuario autenticado aún no está verificado para crear pedidos.
 *
 * @param {{ isOpen: boolean, onClose: () => void }} props
 */
export default function VerificationPendingModal({ isOpen, onClose }) {
    return (
        <Dialog
            open={Boolean(isOpen)}
            onOpenChange={(open) => {
                if (!open) {
                    onClose?.();
                }
            }}
        >
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Verificación pendiente</DialogTitle>
                    <DialogDescription>
                        Tu cuenta aún no está verificada. Un administrador debe
                        aprobar tu perfil antes de que puedas completar pedidos de
                        impresión.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="secondary" onClick={onClose}>
                        Entendido
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
