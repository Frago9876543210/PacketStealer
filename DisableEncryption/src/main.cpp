#include <modloader/statichook.h>
#include <dlfcn.h>
#include <string>

static auto handshake = (size_t) dlsym(dlopen(nullptr, RTLD_LAZY), "_ZTV29ServerToClientHandshakePacket") + 0x10;

struct Packet { size_t vt; };
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

TClasslessInstanceHook(void, _ZN12PacketSender19sendToPrimaryClientERK17NetworkIdentifierRK6Packet, NetworkIdentifier const &nid, Packet const &packet) {
	if (packet.vt != handshake)
		original(this, nid, packet);
}

TClasslessInstanceHook(void, _ZN20EncryptedNetworkPeer16enableEncryptionERKNSt7__cxx1112basic_stringIcSt11char_traitsIcESaIcEEE, std::string const &token) {}
