#include <modloader/statichook.h>
#include <sys/mman.h>
#include <cstring>

//see https://gist.githubusercontent.com/Frago9876543210/7eab63cfd84e59a9b38f861c68fe3c89/raw/7de5ca63e2131093395c0fa9c79d730f29bd0234/removed_thing
extern "C" void modloader_on_server_start(void *serverInstance) {
	auto sym = (size_t) dlsym(RTLD_DEFAULT, "_ZN20ServerNetworkHandler6handleERK17NetworkIdentifierRK11LoginPacket") + 4780;
	mprotect((void *) ((sym) & ~(4096 - 1)), 4096, PROT_READ | PROT_WRITE | PROT_EXEC);
	memset((void *) sym, 0x90, 5571 - 4780);	
}

struct LoginPacket;
struct ClientToServerHandshakePacket;
struct NetworkIdentifier;
struct ServerNetworkHandler {
	void handle(NetworkIdentifier const &, ClientToServerHandshakePacket const &);
};

TInstanceHook(void, _ZN20ServerNetworkHandler6handleERK17NetworkIdentifierRK11LoginPacket, ServerNetworkHandler, NetworkIdentifier const &nid, LoginPacket const &packet) {
	original(this, nid, packet);
	ClientToServerHandshakePacket *pk = nullptr;
	handle(nid, *pk);
}
