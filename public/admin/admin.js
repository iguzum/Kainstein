const deleteModal = document.getElementById("deleteConfirmModal");
const deleteMessage = document.getElementById("deleteConfirmMessage");
const deleteCancel = document.getElementById("deleteCancel");
const deleteConfirm = document.getElementById("deleteConfirm");
let deleteFormToSubmit = null;
let lastDeleteTrigger = null;

function closeDeleteModal() {
    deleteModal.classList.add("hidden");
    deleteModal.classList.remove("flex");
    deleteModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");
    deleteFormToSubmit = null;
    lastDeleteTrigger?.focus();
}

document.querySelectorAll("[data-delete-trigger]").forEach((button) => {
    button.addEventListener("click", () => {
        deleteFormToSubmit = button.closest("form");
        lastDeleteTrigger = button;
        const type = button.dataset.deleteType;
        const name = button.dataset.deleteName;
        deleteMessage.textContent = "Delete " + type + " “" + name + "”? This action cannot be undone.";
        deleteModal.classList.remove("hidden");
        deleteModal.classList.add("flex");
        deleteModal.setAttribute("aria-hidden", "false");
        document.body.classList.add("overflow-hidden");
        deleteCancel.focus();
    });
});

deleteCancel.addEventListener("click", closeDeleteModal);
deleteModal.addEventListener("click", (event) => {
    if (event.target === deleteModal) closeDeleteModal();
});
deleteConfirm.addEventListener("click", () => {
    if (deleteFormToSubmit) deleteFormToSubmit.requestSubmit();
});
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !deleteModal.classList.contains("hidden")) {
        closeDeleteModal();
    }
});
