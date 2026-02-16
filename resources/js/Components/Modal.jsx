import {
    Dialog,
    DialogPanel,
    DialogTitle,
    Transition,
    TransitionChild,
} from '@headlessui/react';

export default function Modal({
    children,
    show = false,
    onClose = () => { },
    closeable = true,

    /* NEW PROPS */
    title = null,
    footer = null,
    size = "2xl",
    position = "center", // center | top
    showClose = true,
    scrollable = true,
    variant = "default", // default | danger | success
}) {

    const close = () => {
        if (closeable) onClose();
    };

    const sizes = {
        sm: "sm:max-w-sm",
        md: "sm:max-w-md",
        lg: "sm:max-w-lg",
        xl: "sm:max-w-xl",
        "2xl": "sm:max-w-2xl",
        "3xl": "sm:max-w-3xl",
        "4xl": "sm:max-w-4xl",
    };

    const variants = {
        default: "",
        danger: "border border-red-300",
        success: "border border-green-300"
    };

    const alignment =
        position === "top"
            ? "items-start pt-20"
            : "items-center";

    return (
        <Transition show={show} leave="duration-200">

            <Dialog
                className={`fixed inset-0 z-50 flex ${alignment} justify-center px-4 py-6`}
                onClose={close}
            >

                {/* backdrop */}
                <TransitionChild
                    enter="ease-out duration-300"
                    enterFrom="opacity-0"
                    enterTo="opacity-100"
                    leave="ease-in duration-200"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                >
                    <div className="fixed inset-0 bg-black/40" />
                </TransitionChild>


                {/* panel */}
                <TransitionChild
                    enter="ease-out duration-300"
                    enterFrom="opacity-0 translate-y-4 sm:scale-95"
                    enterTo="opacity-100 translate-y-0 sm:scale-100"
                    leave="ease-in duration-200"
                    leaveFrom="opacity-100 translate-y-0 sm:scale-100"
                    leaveTo="opacity-0 translate-y-4 sm:scale-95"
                >
                    <DialogPanel
                        className={`
                        w-full transform overflow-hidden rounded-lg bg-white shadow-xl transition-all
                        ${sizes[size]}
                        ${variants[variant]}
                        `}
                    >

                        {/* HEADER */}
                        {(title || showClose) && (
                            <div className="flex items-center justify-between border-b px-5 py-3">
                                {title && (
                                    <DialogTitle className="font-semibold text-lg">
                                        {title}
                                    </DialogTitle>
                                )}

                                {showClose && (
                                    <button
                                        onClick={close}
                                        className="text-gray-400 hover:text-gray-700 text-xl"
                                    >
                                        ×
                                    </button>
                                )}
                            </div>
                        )}

                        {/* BODY */}
                        <div className={`p-5 ${scrollable ? "max-h-[70vh] overflow-y-auto" : ""}`}>
                            {children}
                        </div>

                        {/* FOOTER */}
                        {footer && (
                            <div className="border-t px-5 py-3 bg-gray-50">
                                {footer}
                            </div>
                        )}

                    </DialogPanel>
                </TransitionChild>

            </Dialog>

        </Transition>
    );
}
