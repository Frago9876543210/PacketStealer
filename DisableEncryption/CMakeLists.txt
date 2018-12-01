cmake_minimum_required(VERSION 3.0)
project(DisableEncryption)

set(CMAKE_PREFIX_PATH ../sdk)
set(CMAKE_CXX_STANDARD 17)
set(CMAKE_POSITION_INDEPENDENT_CODE ON)

find_package(ModLoader REQUIRED)

add_library(DisableEncryption SHARED src/main.cpp)
target_link_libraries(DisableEncryption PUBLIC ModLoader)
